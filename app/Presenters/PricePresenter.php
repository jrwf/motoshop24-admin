<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PriceFasade;

class PricePresenter extends BasePresenter
{
	/** @var PriceFasade */
	public PriceFasade $price;

	public function __construct(PriceFasade $price)
	{
		$this->price = $price;
	}

	public function renderDefault()
	{
		$allData = $this->price->getDataToTable();
		bdump($allData, 'all data');
		$this->template->alldata = $allData;
		$this->template->pokus = 'pokus';
	}

	/**
	 * Načítám url z jednotlivých nodů.
	 *
	 * @return void
	 */
	public function renderUrl()
	{
		// Smažu všechno z tabulky url.
		$this->price->deleteAdminUrl();
		// Načtu si url z tabulky admin_rul.
		$getFromAdminUrl = $this->price->selectFromAdminUrl();
		// Uložím je do tabulky admin_url
		if (empty($getFromAdminUrl)) {
			$urls = $this->price->getUrl();
			foreach ($urls as $url) {
				$this->price->insertAdminUrl($url);
			}
		}
	}

	public function renderPrice()
	{
		// Načtu si url z tabulky admin_url.
		$getFromAdminUrl = $this->price->selectFromAdminUrl();
		bdump($getFromAdminUrl, 'get from admin url');

		$db_price = $this->price->getPrices();
		bdump($db_price, 'price');
		$db_all = $this->price->getAll();
//		bdump($db_all, 'db all');
		$db_result = $this->price->getUrl();
//		bdump($db_result);

		$curl = curl_init();
		foreach ($db_price as $item) {
			curl_setopt($curl, CURLOPT_URL, $item['url']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$curl_result = curl_exec($curl);

			// EAN
			$ean_page_content = mb_strpos($curl_result, 'product-core-info__partcode'); // Hledam pozici tridy
			$ean_page_content_first = mb_substr($curl_result, $ean_page_content + 29, 55 ); // Vypisu si retezec od pozice plus 55
			$ean_page_content_end = mb_strpos($ean_page_content_first, '</span>');
			$ean = mb_substr($ean_page_content_first, 0, $ean_page_content_end);
			$eanUp = strtoupper($ean);

			// Price
			$firstPosition = mb_strpos($curl_result, 'product-price-full'); // Hledam pozici tridy
			$content = mb_substr($curl_result, $firstPosition + 46, 55 ); // Vypisu si retezec od pozice plus 55
			$kc_position = mb_strpos($content, 'Kč');
			$price = mb_substr($content, 0, $kc_position - 1);
			$priceFull = str_replace(' ', '', $price);
			$priceFullWithouZeroo = mb_substr($priceFull, 0, -3);
			$all_result = 'price: ' . $priceFullWithouZeroo;
			$selling_price = $priceFullWithouZeroo * 0.95;
			bdump($selling_price, 'selling price 1');
			$selling_price = (int) round($selling_price, 0);
			bdump($selling_price, 'selling price 2');

			$this->price->updatePrice((int) $selling_price, $item['nid']);
			$this->price->insertAllData($item, (int) $priceFullWithouZeroo, (int) $selling_price);
		}

	}
}