<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

class PriceFasade
{
	use Nette\SmartObject;

	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

	public function getPublicArticles()
	{
		return $this->database->table('node')->fetchAll();
	}

	public function getAll()
	{
		return $this->database->query("select distinct 
		node__field_odkaz_na_zdroj.field_odkaz_na_zdroj_uri as url, 
		node__field_odkaz_na_zdroj.entity_id as nid, 
		node__field_cena.field_cena_value as price, 
		node__field_ean.field_ean_target_revision_id as ean_id, 
		paragraph__field_ean.field_ean_value as ean 
		from paragraph__field_ean join node__field_ean on paragraph__field_ean.entity_id = node__field_ean.field_ean_target_id 
		join node__field_cena on node__field_cena.entity_id = node__field_ean.entity_id 
		join node__field_odkaz_na_zdroj on node__field_odkaz_na_zdroj.entity_id = node__field_ean.entity_id")->fetchAll();
	}

	public function getUrl()
	{
		return $this->database->query("select node__field_cena.entity_id, 
											node__field_cena.field_cena_value, 
											node__field_ean.field_ean_target_id, 
											paragraph__field_ean.field_ean_value,
											node__field_odkaz_na_zdroj.field_odkaz_na_zdroj_uri from node__field_cena join node__field_ean on node__field_cena.entity_id = node__field_ean.entity_id join paragraph__field_ean on node__field_ean.field_ean_target_id = paragraph__field_ean.entity_id join node__field_odkaz_na_zdroj on node__field_odkaz_na_zdroj.entity_id = node__field_ean.field_ean_target_id")->fetchAll();
	}
}