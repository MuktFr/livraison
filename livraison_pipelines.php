<?php
/**
 * Utilisations de pipelines par Livraison
 *
 * @plugin     Livraison
 * @copyright  2015
 * @author     Cédric
 * @licence    GNU/GPL
 * @package    SPIP\Livraison\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;
	

function livraison_afficher_contenu_objet($flux){

	if ($flux['args']['type']=='commande'
	  AND $id_commande = $flux['args']['id_objet']){

		$adresse = recuperer_fond("prive/objets/contenu/commande-adresse_livraison",array('id_commande'=>$id_commande));

		if ($p = strpos($flux['data'],"</table>")){
			$flux['data'] = substr_replace($flux['data'],$adresse,$p+8,0);
		}
		else {
			$flux['data'] .= $adresse;
		}
	}
	return $flux;
}


/**
 * Ajout de contenu sur certaines pages,
 * notamment des formulaires de liaisons entre objets
 *
 * @pipeline affiche_milieu
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function livraison_affiche_milieu($flux) {
	$texte = "";
	$e = trouver_objet_exec($flux['args']['exec']);


	// livres sur les produits et offres abonnement
	if (!$e['edition']
		and $table = $e['table_objet_sql']
		and $primary = $e['id_table_objet']
	  and $id = intval($flux['args'][$primary])) {
		$objet = sql_fetsel("*",$table,"$primary=".$flux['args'][$primary]);
		// si l'objet est livrable (pas immateriel) proposer le choix des modes de livraison
		if (isset($objet['immateriel']) and !$objet['immateriel']){
			$flux['data'] .= recuperer_fond('prive/squelettes/inclure/liens-livraisonmodes', array(
				'table_source' => 'livraisonmodes',
				'objet' => $e['type'],
				'id_objet' => $flux['args'][$e['id_table_objet']]
			));
		}
	}

	if ($texte) {
		if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
			$flux['data'] = substr_replace($flux['data'],$texte,$p,0);
		else
			$flux['data'] .= $texte;
	}

	return $flux;
}

/**
 * Renseigner les infos de facturation liee a une commande
 * @param array $flux
 * @return array mixed
 */
function livraison_bank_dsp2_renseigner_facturation($flux) {

	if (isset($flux['args']['id_transaction'])
		and $id_transaction = intval($flux['args']['id_transaction'])
		and isset($flux['args']['id_commande'])
		and $id_commande = intval($flux['args']['id_commande']) ) {

		$commande = sql_fetsel("*", "spip_commandes", "id_commande=".intval($id_commande));
		if ($commande) {
			$quoi = '';
			if (isset($commande['facturation_nom']) and $commande['facturation_nom']) {
				$quoi = 'facturation';
			}
			elseif (isset($commande['livraison_nom']) and $commande['livraison_nom']) {
				$quoi = 'livraison';
			}
			if ($quoi) {
				if ($commande[$quoi.'_nom']) {
					$flux['data']['nom'] = $commande[$quoi.'_nom'];
				}
				if ($commande[$quoi.'_adresse']) {
					$flux['data']['adresse'] = $commande[$quoi.'_adresse'];
				}
				if ($commande[$quoi.'_societe']) {
					$flux['data']['adresse'] = trim($commande[$quoi.'_societe'] . "\n" . $flux['data']['adresse']);
				}
				if ($commande[$quoi.'_adresse_cp']) {
					$flux['data']['code_postal'] = $commande[$quoi.'_adresse_cp'];
				}
				if ($commande[$quoi.'_adresse_ville']) {
					$flux['data']['ville'] = $commande[$quoi.'_adresse_ville'];
				}
				if ($commande[$quoi.'_adresse_pays']) {
					$flux['data']['pays'] = $commande[$quoi.'_adresse_pays'];
				}
			}
		}

	}
	return $flux;
}