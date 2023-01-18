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

	/**
	 * @return array
	 */
	public function getPublicArticles(): array
	{
		return $this->database->table('node')->fetchAll();
	}

	/**
	 * @param int $price
	 * @param int $nid
	 *
	 * @return void
	 */
	public function updatePrice(int $price, int $nid)
	{
		bdump($price, 'update price');
		$this->database->query("update node_revision__field_cena set field_cena_value = ? where entity_id = ?", $price, $nid);
		$this->database->query("update node__field_cena set field_cena_value = ? where entity_id = ?", $price, $nid);
	}

	/**
	 * Ukládají se data pro aktuální den.
	 *
	 * @param $data
	 *
	 * @return void
	 */
	public function insertCurrentData($data)
	{
		foreach ($data as $item) {
			// TODO - Upravit jako hromadné ukládání.
			$this->database->query("insert into admin_prices (nid,url,price_previous) values (?, ?, ?)", $item['nid'], $item['url'], $item['price']);
		}
	}

	/**
	 * Načítám data z aktuálního dne jako kontrola jestli už se neukládaly nové ceny.
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function selectCurrentData(int $limit = 10): array
	{
		$date = new \DateTime();
		$dateDay = $date->format('Y-m-d');
		return $this->database->query('select * from admin_prices where DATE_FORMAT(created, "%Y-%m-%d") = ? and status_data = 0 limit ?', $dateDay, $limit)->fetchAll();
	}

	public function updateCurrentData(int $price_new, int $selling_price, int $nid)
	{
		$this->database->query("update admin_prices set price_new = ?, selling_price = ?, status_data = 1 where nid = ? ", $price_new, $selling_price, $nid);
	}

	/**
	 * @param $data
	 * @param int $price_new
	 * @param int $selling_price
	 *
	 * @return void
	 */
	public function insertAllData($data, int $price_new, int $selling_price)
	{
		$this->database->query("insert into admin_prices (nid,url,price_previous,price_new,selling_price) values (?, ?, ?, ?,?)", $data['nid'], $data['url'], $data['price'], $price_new, $selling_price);
	}

	/**
	 * @param $data
	 *
	 * @return void
	 */
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

	/**
	 * @return void
	 */
	public function deleteAdminUrl()
	{
		$this->database->table('admin_url')
						->delete();
	}

	/**
	 * Načítá url, nid a ceny z produktů.
	 *
	 * @return array
	 */
	public function getPrices(): array
	{
		return $this->database->query("select 
		node__field_odkaz_na_zdroj.field_odkaz_na_zdroj_uri as url, 
		node__field_odkaz_na_zdroj.entity_id as nid, 
		node__field_cena.field_cena_value as price 
		from node__field_odkaz_na_zdroj join node__field_cena on node__field_odkaz_na_zdroj.entity_id = node__field_cena.entity_id")->fetchAll();
	}

	/**
	 * @return array
	 */
	public function getAll(): array
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

	/**
	 * @return array
	 */
	public function getUrl(): array
	{
		return $this->database->query("select node__field_cena.entity_id, 
											node__field_cena.field_cena_value, 
											node__field_ean.field_ean_target_id, 
											paragraph__field_ean.field_ean_value,
											node__field_odkaz_na_zdroj.field_odkaz_na_zdroj_uri 
											from node__field_cena 
											join node__field_ean on node__field_cena.entity_id = node__field_ean.entity_id 
											join paragraph__field_ean on node__field_ean.field_ean_target_id = paragraph__field_ean.entity_id 
											join node__field_odkaz_na_zdroj on node__field_odkaz_na_zdroj.entity_id = node__field_ean.field_ean_target_id")->fetchAll();
	}

	/**
	 * @return array
	 */
	public function getDataToTable(): array
	{
		return $this->database->query("select nid as id, url, price_previous, price_new, selling_price, created, status_data, 
										   (select title from node_field_data where nid = id) as title
											from admin_prices group by nid")->fetchAll();
	}
}