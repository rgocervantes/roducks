<?php
/**
 *
 * This file is part of Roducks.
 *
 *    Roducks is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Roducks is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with Roducks.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use App\Sites\Rod\Modules\Finances\Helper\Finances as FinancesHelper;

return [
	[
		'link' => FinancesHelper::getUrl(), 
		'text' => "Finanzas", 
		'access' => "all",
		'icon' => "stats"
	],
	[
		'link' => FinancesHelper::getPaymentsUrl(), 
		'text' => "Pagos", 
		'access' => "all",
		'icon' => "credit-card"
	],
	[
		'link' => FinancesHelper::getShoppingUrl(), 
		'text' => "Compras Normales", 
		'access' => "all",
		'icon' => "usd"
	],	
	[
		'link' => FinancesHelper::getScheduledUrl(), 
		'text' => "Compras a MSI", 
		'access' => "all",
		'icon' => "calendar"
	],
	[
		'separator' => "divider"
	],	
	[
		'link' => FinancesHelper::getAddUrl(), 
		'text' => "Nueva Compra", 
		'access' => "all",
		'icon' => "shopping-cart"
	],
	[
		'link' => FinancesHelper::getSimulationUrl(), 
		'text' => "Simulation", 
		'access' => "all",
		'icon' => "scale"
	],	
];

