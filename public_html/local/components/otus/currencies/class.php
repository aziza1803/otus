<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Currency;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Класс компонента для отображения курсов валют.
 */
class CurrencyRatesComponent extends \CBitrixComponent
{
	/**
	 * Проверка подключения обязательных модулей.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	private function checkModules(): bool
	{
		if (!Loader::includeModule('currency'))
		{
			// Выбрасываем исключение, которое будет поймано в executeComponent
			throw new Main\LoaderException(Loc::getMessage('CURRENCY_MODULE_NOT_INSTALLED'));
		}
		return true;
	}

	/**
	 * Подготовка и валидация входных параметров компонента.
	 * Вызывается автоматически до executeComponent.
	 * @param array $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams): array
	{
		// 1. Валюты для конвертации
		$arParams['arrCURRENCY_FROM'] = $arParams['arrCURRENCY_FROM'] ?? [];
		if (!is_array($arParams['arrCURRENCY_FROM']))
		{
			$arParams['arrCURRENCY_FROM'] = [];
		}
		$arParams['arrCURRENCY_FROM'] = array_filter($arParams['arrCURRENCY_FROM']);

		// 2. Базовая валюта
		$arParams['CURRENCY_BASE'] = trim((string)($arParams['CURRENCY_BASE'] ?? ''));

		// 3. Дата курса
		$arParams['RATE_DAY'] = trim((string)($arParams['RATE_DAY'] ?? ''));

		// 4. Показывать курсы ЦБ
		$arParams['SHOW_CB'] = ($arParams['SHOW_CB'] ?? null) === 'Y' ? 'Y' : 'N';
		// Отключаем опцию, если базовая валюта не рубль
		if ($arParams['CURRENCY_BASE'] !== 'RUB' && $arParams['CURRENCY_BASE'] !== 'RUR')
		{
			$arParams['SHOW_CB'] = 'N';
		}
		
		// 5. Время кэширования
		$arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 86400);
		
		return $arParams;
	}

	/**
	 * Определяет базовую валюту, используя несколько источников.
	 */
	private function determineBaseCurrency(): void
	{
		if ($this->arParams['CURRENCY_BASE'] === '')
		{
			$this->arParams['CURRENCY_BASE'] = Option::get('sale', 'default_currency');
		}

		if ($this->arParams['CURRENCY_BASE'] === '')
		{
			$this->arParams['CURRENCY_BASE'] = Currency\CurrencyManager::getBaseCurrency();
		}
	}

	/**
	 * Определяет дату для получения курсов и сохраняет в arResult.
	 */
	private function determineRateDate(): void
	{
		if ($this->arParams['RATE_DAY'] === '')
		{
			$this->arResult['RATE_DAY_TIMESTAMP'] = time();
		}
		else
		{
			if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $this->arParams['RATE_DAY'], $parsed))
			{
				$year = (int)$parsed[1];
				// Проверка года на валидность для 32-битного timestamp
				if ($year >= 1901 && $year <= 2038)
				{
					$this->arResult['RATE_DAY_TIMESTAMP'] = mktime(0, 0, 0, (int)$parsed[2], (int)$parsed[3], $year);
				}
			}
		}

		// Если timestamp не был установлен, используем текущее время
		$this->arResult['RATE_DAY_TIMESTAMP'] = $this->arResult['RATE_DAY_TIMESTAMP'] ?? time();
		$this->arResult['RATE_DAY_SHOW'] = ConvertTimeStamp($this->arResult['RATE_DAY_TIMESTAMP'], 'SHORT');
	}

	/**
	 * Получает курсы из базы данных Bitrix.
	 * @return array - Список валют для тегирования кэша.
	 */
	private function fetchDbRates(): array
	{
		$currencyListForCache = [];
		$this->arResult['CURRENCY'] = [];

		if (empty($this->arParams['arrCURRENCY_FROM']))
		{
			return [];
		}

		$iterator = Currency\CurrencyTable::getList([
			'select' => ['CURRENCY', 'AMOUNT_CNT'],
			'filter' => ['@CURRENCY' => $this->arParams['arrCURRENCY_FROM']],
			'order'  => ['CURRENCY' => 'ASC']
		]);

		while ($row = $iterator->fetch())
		{
			$currencyListForCache[$row['CURRENCY']] = $row['CURRENCY'];

			$rate = \CCurrencyRates::ConvertCurrency(
				$row['AMOUNT_CNT'],
				$row['CURRENCY'],
				$this->arParams['CURRENCY_BASE'],
				$this->arParams['RATE_DAY']
			);

			$this->arResult['CURRENCY'][] = [
				'FROM' => \CCurrencyLang::CurrencyFormat($row['AMOUNT_CNT'], $row['CURRENCY'], true),
				'BASE' => \CCurrencyLang::CurrencyFormat($rate, $this->arParams['CURRENCY_BASE'], true),
			];
		}

		return $currencyListForCache;
	}

	/**
	 * Регистрирует теги для управляемого кэша.
	 * @param array $currencyList
	 */
	private function registerCacheTags(array $currencyList): void
	{
		if (empty($currencyList) || !defined('BX_COMP_MANAGED_CACHE'))
		{
			return;
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->StartTagCache($this->getCachePath());

		// Добавляем базовую валюту в список для тегирования
		$currencyList[$this->arParams['CURRENCY_BASE']] = $this->arParams['CURRENCY_BASE'];

		foreach ($currencyList as $currency)
		{
			$CACHE_MANAGER->RegisterTag('currency_id_' . $currency);
		}

		$CACHE_MANAGER->EndTagCache();
	}

	/**
	 * Главный метод, точка входа в компонент.
	 */
	public function executeComponent()
	{
		try
		{
			$this->checkModules();

			if ($this->startResultCache())
			{
				// Логика выполняется только если кэш недействителен
				$this->determineBaseCurrency();
				$this->determineRateDate();

				$currencyList = $this->fetchDbRates();
				// $this->fetchCbrfRates();

				$this->registerCacheTags($currencyList);
				
				// Подключаем шаблон
				$this->includeComponentTemplate();
			}
		}
		catch (\Exception $e)
		{
			// В случае ошибки (например, не подключен модуль) сбрасываем кэш и показываем ошибку
			$this->abortResultCache();
			ShowError($e->getMessage());
		}
	}
}