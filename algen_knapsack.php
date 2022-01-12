
<?php
set_time_limit(1000000);
class Parameters
{
	const FILE_NAME = 'products.txt'; /*untuk membaca file*/
	const COLUMNS = ['item', 'price']; /*membuat kolom item dan harga*/
	const POPULATION_SIZE = 10;
	const BUDGET = 280000;
}

class Catalogue /*class untuk membaca file produk yg telah dibuat sebelumnya*/
{
	function createProductColumn($listOfRawProduct){ /*untuk mengubah index 0,1 menjadi item price*/
		foreach (array_keys($listOfRawProduct) as $listOfRawProductkey){
			$listOfRawProduct[Parameters::COLUMNS[$listOfRawProductkey]] = $listOfRawProduct[$listOfRawProductkey];
			unset($listOfRawProduct[$listOfRawProductkey]);
		}
		return $listOfRawProduct;
	} 

	function product(){ /*memanggil file*/
		$collectionOfListProduct = [];
		$raw_data = file(Parameters::FILE_NAME);
		foreach ($raw_data as $listOfRawProduct) { /* untuk membaca setiap baris item produk*/
			$collectionOfListProduct[] = $this ->createProductColumn(explode(",", $listOfRawProduct));
		}
		return $collectionOfListProduct;
	}
}

class Individu 
{
	function countNumberOfGen()
	{
		$catalogue = new Catalogue;
		return count($catalogue->product());
	}

	function createRandomIndividu()
	{
		for ($i = 0; $i <= $this->countNumberOfGen()-1; $i++){
			$ret[] = rand(0, 1);
		}
		return $ret;
	}
}

class Population
{
	function createRandomPopulation(){
		$individu = new Individu;
		for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
			$ret[] = $individu->createRandomIndividu();
		}
		return $ret;
	}
}

class Fitness
{
	function selectingItem($individu)
	{
		$catalogue = new Catalogue;
		foreach($individu as $individukey => $binaryGen){
			if ($binaryGen === 1){
				$ret[] = [
					'selectedKey' => $individukey,
					'selectedPrice' => $catalogue->product()[$individukey]['price']
				];
			}
		}
		return $ret;
	} 

	function calculateFitnessValue($individu)
	{
		return array_sum(array_column($this->selectingItem($individu),'selectedPrice'));
	}

	function coutnSelectedItem($individu)
	{
		return count($this->selectingItem($individu));
	}

	function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)
	{
		if ($numberOfIndividuHasMaxItem === 1){
			$index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
			echo 'individu = 1';
			print_r($fits[$index]);
		}else {
			foreach ($fits as $key => $val){
				if ($val['numberOfSelectedItem'] === $maxItem){
					echo $key.' '.$val['fitnessValue'].'<br>';
					$ret[] = [
						'individukey' => $key,
						'fitnessValue' => $val['fitnessValue']
					];
				}
			}
			if (count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
				$index = rand(0, count($ret) - 1);
			}else {
				$max = max(array_column($ret, 'fitnessValue'));
				$index = array_search($max, array_column($ret, 'fitnessValue'));
			}
			echo 'individu = 2';
			print_r($ret[$index]);
		}
	}

	function isFound($fits)
	{
		$countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
		print_r($countedMaxItems);
		echo '<br>';
		$maxItem = max(array_keys($countedMaxItems));
		echo $maxItem;
		echo '<br';
		echo $countedMaxItems[$maxItem];
		$numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

		//$this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem);

		print_r ($this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem));
		exit;
	}

	function isFit($fitnessValue)
	{
		if ($fitnessValue <= Parameters::BUDGET){
			return TRUE;
		}
	}

	function fitnessEvaluation($population)
	{
		$catalogue = new Catalogue;
		foreach ($population as $listOfIndividuKey => $listOfIndividu){
			echo 'Individu-'. $listOfIndividuKey.'<br>';
			foreach ($listOfIndividu as $individukey => $binaryGen){
				echo $binaryGen.'&nbsp;&nbsp;';
				print_r($catalogue->product()[$individukey]);
				echo '<br>';
			}
			$fitnessValue = $this->calculateFitnessValue($listOfIndividu);
			$numberOfSelectedItem = $this->coutnSelectedItem($listOfIndividu);
			echo 'Max. Item : '.$numberOfSelectedItem;
			echo ' Fitness value : '.$fitnessValue; 
			if ($this->isFit($fitnessValue)){
				echo ' (Fit)';
				$fits[] = [
					'selectedIndividuKey' => $listOfIndividu,
					'numberOfSelectedItem' => $numberOfSelectedItem,
					'fitnessValue' => $fitnessValue
				];
				print_r($fits);
			}else{
				echo ' (Not Fit)';
			} 
			echo '<p>';
		}
		$this->isFound($fits);
	}
}

$parameters = [ /*untuk menyimpan nilai2 parameter yang akan digunakan*/
	'file_name' => 'products.txt', /*untuk membaca file*/
	'columns' => ['item', 'price'], /*membuat kolom item dan harga*/
	'population_size' => 10
];

// $katalog = new Catalogue;
// $katalog->product($parameters);

$initialPopulation = new Population;
$population = $initialPopulation->createRandomPopulation();

$fitness = new Fitness;
$fitness->fitnessEvaluation($population);
//print_r($population);

// $individu = new Individu;
// print_r($individu->createRandomIndividu()); 