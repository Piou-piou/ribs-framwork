<header>
	<div class="inner">
		<h1>Configuration | Infos Générales</h1>
	</div>
</header>

<link rel="stylesheet" type="text/css" href="<?=LIBSWEBROOT?>popup/css/style.css">
<script src="<?=LIBSWEBROOT?>popup/js/popup.js"></script>
<link rel="stylesheet" type="text/css" href="<?=LIBSWEBROOT?>checkbox/css/style.css">
<script src="<?=LIBSWEBROOT?>checkbox/js/anim.js"></script>
<?php require_once(ROOT."admin/views/configuration/js/infos_generales.php");?>
<?php require_once('header.php');?>

<div class="inner">
	<?php require_once(LIBSROOT."barre_chargement/index.php");?>
	<a class="submit-contenu supprimer-page link" href="<?=ADMWEBROOT?>configuration/index"><i class="fa fa-times"></i>Annuler</a>

	<div class="contenu modifier-contenu gestion-comptes">
		<table>
			<tr>
				<td>Le site est il toujours en développement</td>
				<td>
					<label for="developpement" class="checkbox-perso switched">
						<input type="checkbox" class="test-check" id="developpement" <?php if ($developpement == 1): ?>checked<?php endif;?>>
					</label>
				</td>
			</tr>
		</table>
	</div>

	<form action="<?=ADMWEBROOT?>controller/core/admin/configuration/modifier" method="post">
		<div class="contenu modifier-contenu">

			<h2>Gestion des infos de développement</h2>

			<p>Toutes ces informations seront utilisées uniquement quand le site est en développement</p>

			<button type="submit" class="submit-contenu" type="submit"><i class="fa fa-check"></i>Valider</button>

			<h3>Informations relatives à la base de données</h3>
			<div>
				<div class="colonne">
					<div class="bloc">
						<label for="db_type_dev" class="label"  data-error="Le type de base de données doit être entre 3 et 15 caractères">Type de votre base de données (Mysql, postgreSQL, ...)</label>
						<input type="text" name="db_type_dev" type-val="string" min="3" max="15" value="<?=$db_type_dev?>">
					</div>
				</div>
				<div class="colonne">
					<div class="bloc">
						<label for="db_name_dev" class="label"  data-error="L'url de votre site doit être entre 3 et 90 caractères">Nom de la base de données de votre site</label>
						<input type="text" name="db_name_dev" type-val="string" min="3" max="90" value="<?=$db_name_dev?>">
					</div>
				</div>
			</div>

			<div>
				<div class="colonne">
					<div class="bloc">
						<label for="db_user_dev" class="label"  data-error="Le nom d'utilisateur pour vous connecter à la base de données doit être entre 3 et 50 caractères">Nom d'utilisateur utilisé pour votre base de données</label>
						<input type="text" name="db_user_dev" type-val="string" min="3" max="50" value="<?=$db_user_dev?>">
					</div>
				</div>
				<div class="colonne">
					<div class="bloc">
						<label for="db_pass_dev" class="label"  data-error="Le nom d'utilisateur pour vous connecter à la base de données doit être entre 3 et 90 caractères">mot de passe utilisé pour votre base de données</label>
						<input type="text" name="db_pass_dev" type-val="string" min="3" max="90" value="<?=$db_pass_dev?>">
					</div>
				</div>
			</div>

			<div>
				<div class="colonne">
					<div class="bloc">
						<label for="db_host_dev" class="label"  data-error="L'adresse du serveur qui héberge votre base de données doit être entre 3 et 90 caractères">Adresse du serveur hébergeant la base de données</label>
						<input type="text" name="db_host_dev" type-val="string" min="3" max="90" value="<?=$db_host_dev?>">
					</div>
				</div>
			</div>
		</div>
	</form>
</div>