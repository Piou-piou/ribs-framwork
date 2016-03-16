<?php
	namespace core\admin;

	use core\App;
	use core\auth\Encrypt;
	use core\auth\Membre;
	use core\Configuration;
	use core\functions\ChaineCaractere;
	use core\HTML\flashmessage\FlashMessage;
	use core\mail\Mail;

	class Admin extends Membre {
		private $acces_admin;


		//-------------------------- CONSTRUCTEUR ----------------------------------------------------------------------------//
		public function __construct($id_identite) {
			$dbc = \core\App::getDb();

			//on récupere le lvl de l'admin
			$query = $dbc->query("SELECT acces_admin FROM identite WHERE ID_identite=".$id_identite);
			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					$this->acces_admin = $obj->acces_admin;
				}
			}

			//si on ne passe pas dans le foreach -> on est pas admin donc on deco le compte
			if ((!isset($this->acces_admin)) || ($this->acces_admin != 1)) {
				FlashMessage::setFlash("Vous n'êtes pas un administrateur, vous ne pouvez pas accéder à cette page");
				header("location:".WEBROOT."index.php");
			}
		}
		//-------------------------- FIN CONSTRUCTEUR ----------------------------------------------------------------------------//



		//-------------------------- GETTER ----------------------------------------------------------------------------//
		public function getAccesAdmin() {
			return $this->acces_admin;
		}


		/**
		 * Pour récupérer la liste de tous les users afin d'activer un compte ou modifier des trucs dessus
		 * si archiver == null on récupère les utilisateurs actifs sur le site sinon on récupere les utilisateurs archives
		 */
		public function getAllUser($archiver = null) {
			$dbc = \core\App::getDb();
			$this->setAllUser(null, null, null, null, null, null, null);

			if ($archiver == null) {
				$query = $dbc->query("SELECT * FROM identite WHERE archiver IS NULL AND ID_identite > 1");
			}
			else {
				$query = $dbc->query("SELECT * FROM identite WHERE archiver IS NOT NULL AND ID_identite > 1");
			}

			$config = new Configuration();

			if ((is_array($query)) && (count($query) > 0)) {
				$id_identite = [];
				$nom = [];
				$prenom = [];
				$pseudo = [];
				$mail = [];
				$img_profil = [];
				$valide = "";

				foreach ($query as $obj) {
					$id_identite[] = $obj->ID_identite;
					$nom[] = $obj->nom;
					$prenom[] = $obj->prenom;
					$pseudo[] = $obj->pseudo;
					$mail[] = $obj->mail;
					$img_profil[] = $obj->img_profil;

					if (($config->getValiderInscription() == 1) && ($obj->valide == 0)) {
						$valide[] = "<a href=".ADMWEBROOT."controller/core/admin/comptes/valider_compte?id_identite=$obj->ID_identite>Valider cet utilisateur</a>";
					}
					else {
						$valide[] = "Utilisateur validé";
					}
				}

				$this->setAllUser($id_identite, $nom, $prenom, $mail, $pseudo, $img_profil, $valide);
			}
		}

		/**
		 * Fonctio qui premet de setter les différents élément d'un user
		 * @param $id_identite
		 */
		public function getunUser($id_identite) {
			$dbc = \core\App::getDb();

			$query = $dbc->query("SELECT * FROM identite WHERE ID_identite=".$id_identite);

			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					$this->id_identite = $obj->ID_identite;
					$this->nom = $obj->nom;
					$this->prenom = $obj->prenom;
					$this->img = $obj->img_profil;
					$this->mail = $obj->mail;
					$this->valide = $obj->valide;
				}
			}
		}

		/**
		 * fonction qui si égale a 1 alors il y a une notification dans l'admin du site
		 * @return mixed
		 */
		public function getNotification() {
			$dbc = App::getDb();

			$query = $dbc->query("SELECT admin FROM notification");

			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					return $obj->admin;
				}
			}
		}
		//-------------------------- FIN GETTER ----------------------------------------------------------------------------//



		//-------------------------- SETTER ----------------------------------------------------------------------------//

		/**
		 * @param null|string $valide
		 */
		private function setAllUser($id_identite, $nom, $prenom, $mail, $pseudo, $img_profil, $valide) {
			$this->id_identite = $id_identite;
			$this->nom = $nom;
			$this->prenom = $prenom;
			$this->mail = $mail;
			$this->pseudo = $pseudo;
			$this->img = $img_profil;
			$this->valide = $valide;
		}

		/**
		 * Fonction qui permet de valider un compte utilisateur pour qu'il puisse se conecter au site
		 * @param $id_identite
		 */
		public function setValideCompte($id_identite) {
			$dbc = \core\App::getDb();

			$value = array("id_identite" => $id_identite);

			$dbc->prepare("UPDATE identite SET valide=1 WHERE ID_identite=:id_identite", $value);

			$this->getunUser($id_identite);
		}

		/**
		 * fonction quir genere un mot de passe aleatoire pour le compte spécifié en param
		 * @param $id_identite
		 */
		public function setReinitialiserMdp($id_identite) {
			$dbc = \core\App::getDb();

			$this->getunUser($id_identite);

			if (($this->mail != "") || ($this->mail != null)) {
				$mdp = ChaineCaractere::random(6);
				$mdp_encode = Encrypt::setEncryptMdp($mdp, $id_identite);

				$value = array(
					"mdp" => $mdp_encode,
					"id_identite" => $id_identite,
					"last_change_mdp" => date("Y-m-d")
				);

				FlashMessage::setFlash("Mot de passe réinitialisé avec succès ! L'utilisateur à reçu un E-mail avec son nouveau mot de passe", "success");

				$dbc->prepare("UPDATE identite SET mdp=:mdp, last_change_mdp=:last_change_mdp WHERE ID_identite=:id_identite", $value);

				$mail = new Mail($this->mail);
				$mail->setEnvoyerMail("Réinitialisation de votre E-mail effectuée", "Votre mot de passe a été réinitialisé");
			}
			else {
				FlashMessage::setFlash("le mot de passe de $this->pseudo ne peu pas être réinitialisé car il ne possède pas d'E-mail");
				$this->erreur = true;
			}
		}

		/**
		 * Supprime le compte en question et enleve l'image de profil aussi
		 * @param $id_identite
		 */
		public function setArchiverCompte($id_identite) {
			$dbc = \core\App::getDb();

			$value = array(
				"id_identite" => $id_identite,
				"archiver" => 1
			);

			$dbc->prepare("UPDATE identite SET archiver=:archiver WHERE ID_identite=:id_identite", $value);
		}

		/**
		 * Supprime le compte en question et enleve l'image de profil aussi
		 * @param $id_identite
		 */
		public function setActiverCompte($id_identite) {
			$dbc = \core\App::getDb();

			$value = array(
				"id_identite" => $id_identite,
				"archiver" => NULL
			);

			$dbc->prepare("UPDATE identite SET archiver=:archiver WHERE ID_identite=:id_identite", $value);
		}

		/**
		 * Supprime le compte en question et enleve l'image de profil aussi
		 * @param $id_identite
		 */
		public function setSupprimerCompte($id_identite) {
			$dbc = \core\App::getDb();

			$oldimg_profil = "";

			//test si il y a deja une img
			$query = $dbc->query("SELECT img_profil FROM identite where ID_identite=$id_identite");

			if ((is_array($query)) && (count($query) > 0)) {
				foreach ($query as $obj) {
					$oldimg_profil = $obj->img_profil;
				}
			}

			$oldimg_profil = explode("/", $oldimg_profil);
			if (end($oldimg_profil) != "defaut.png") {
				unlink("../../images/profil/".$oldimg_profil[7]);
			}

			$value = array(
				"id_identite" => $id_identite
			);

			$dbc->prepare("DELETE FROM identite WHERE ID_identite=:id_identite", $value);
		}

		/**
		 * permet de dire qu'on a vue une notification dans l'administration du site internet
		 */
		public static function setNotificationVue() {
			$dbc = App::getDb();

			$value = [
				"admin" => 0,
				"id" => 1
			];

			$dbc->prepare("UPDATE notification SET admin=:admin WHERE ID_notification=:id", $value);
		}
		//-------------------------- FIN SETTER ----------------------------------------------------------------------------//



	}