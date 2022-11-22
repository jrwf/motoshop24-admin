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
		$this->template->pokus = 'pokus';
	}

	public function renderPrice()
	{
		$db_all = $this->price->getAll();
		bdump($db_all, 'db all');
		$db_result = $this->price->getUrl();
//		bdump($db_result);

		$curl = curl_init();
		foreach ($db_all as $item) {
//			curl_setopt($curl, CURLOPT_URL, 'https://www.yamaha-motor.eu/cz/cs/products/motorcycles/supersport/r1m-2022/accessories/rear-seat-bag/yme-rearb-ag-01/#/');
			curl_setopt($curl, CURLOPT_URL, $item['url']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$curl_result = curl_exec($curl);

			// EAN
			$ean_page_content = mb_strpos($curl_result, 'product-core-info__partcode'); // Hledam pozici tridy
			$ean_page_content_first = mb_substr($curl_result, $ean_page_content + 29, 55 ); // Vypisu si retezec od pozice plus 55
			$ean_page_content_end = mb_strpos($ean_page_content_first, '</span>');
			$ean = mb_substr($ean_page_content_first, 0, $ean_page_content_end);
//			bdump($ean, 'ean');
//			bdump($item['field_ean_value'], 'parcode');
			$spolu = 'ean: ' . $item['ean'] . ' a parcode: ' . $ean;
//			bdump($spolu);
			$eanUp = strtoupper($ean);
			bdump($item['ean'], 'ean');
			bdump($eanUp, 'up');
//			bdump($ean_page_content, 'ean content');
//			bdump($ean_page_content_first, 'ean first');
//			bdump($ean_page_content_end, 'end');
//			bdump($ean, 'ean');

			// Price
			$firstPosition = mb_strpos($curl_result, 'product-price-full'); // Hledam pozici tridy
			$content = mb_substr($curl_result, $firstPosition + 46, 55 ); // Vypisu si retezec od pozice plus 55
			$kc_position = mb_strpos($content, 'Kč');
			$price = mb_substr($content, 0, $kc_position - 1);
			$priceFull = str_replace(' ', '', $price);
//			bdump($priceFull, 'price');
			if ($eanUp !== $item['ean']) {
//				$all_result = 'Puvodni cena:' . $item['price'] . ', aktulni cena: ' . $priceFull . ' Ean na webu je: ' . $item['ean'] . ' a na yamaze: ' . $ean . '<br />' . $item['url'];
//				bdump($all_result, 'all');
			}
		}

	}
}