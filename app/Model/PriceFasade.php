<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Database\ResultSet;

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

	public function updatePrice(int $price, int $nid)
	{
		bdump($price, 'update price');
		$this->database->query("update node_revision__field_cena set field_cena_value = ? where entity_id = ?", $price, $nid);
//		$this->database->query("update node__field_cena set field_cena_value = ? where entity_id = ?", $price, $nid);
	}

	public function insertAllData($data, int $price_new, int $selling_price)
	{
		$this->database->query("insert into admin_prices (nid,url,price_previous,price_new,selling_price) values (?, ?, ?, ?,?)", $data['nid'], $data['url'], $data['price'], $price_new, $selling_price);
	}

	public function insertAdminUrl($data)
	{
		$this->database->table('admin_url')
			->insert([
				'nid' => $data['entity_id'],
				'url' => $data['field_odkaz_na_zdroj_uri'],
			]);
	}

	/**
	 * Načítám všechny položky z databáze admin_url
	 *
	 * @return array
	 */
	public function selectFromAdminUrl(): array
	{
		return $this->database->table('admin_url')
						->fetchAll();
	}

	public function deleteAdminUrl()
	{
		$this->database->table('admin_url')
						->delete();
	}

	public function getPrices()
	{
		return $this->database->query("select 
		node__field_odkaz_na_zdroj.field_odkaz_na_zdroj_uri as url, 
		node__field_odkaz_na_zdroj.entity_id as nid, 
		node__field_cena.field_cena_value as price 
		from node__field_odkaz_na_zdroj join node__field_cena on node__field_odkaz_na_zdroj.entity_id = node__field_cena.entity_id")->fetchAll();
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

	/**
	 * @return array
	 */
	public function getDataToTable(): array
	{
		return $this->database->query("select 
											nid as id, url, price_previous, price_new, selling_price, created, (select title from node_field_data where nid = id) as title
											from admin_prices group by nid")->fetchAll();
	}
}