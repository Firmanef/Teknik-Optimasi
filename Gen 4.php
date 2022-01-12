<?php

Class Parameters{
    const FILE_NAME = 'produk.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 30;
    const BUDGET =280000;
    const STOPING_VALUE = 10000;
    const CROSSOVERRATE = 0.8;
}


class Catalogue
{
    
    function createProductColumn($listOfRawProduct){
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product(){
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::FILE_NAME);
        foreach ($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this -> createProductColumn(explode(",",$listOfRawProduct));
        }

        // foreach($collectionOfListProduct as $listOfRawProduct){
        //     print_r($listOfRawProduct);
        //     echo '<br>';
        // }

        return $collectionOfListProduct;

    }
}

Class individu{
    function countNumberOfGen(){
        $catalogue = new Catalogue;
        return count($catalogue -> product());
    }

    function createRandomIndividu(){
        for ($i=0;$i<= $this->countNumberOfGen() - 1;$i++){
            $ret[] = rand(0,1);
        }
        return $ret;
        
    }
}

class Population{
  
    function createRandomPopulation(){
        $individu = new individu;
        for($i = 0;$i <= Parameters::POPULATION_SIZE - 1 ; $i++){
           $ret[] =  $individu -> createRandomIndividu();
        }
        return $ret;
        
    }
}

class Fitness{
    function selectingItem($individu){
        $catalogue = new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if($binaryGen === 1){
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue -> product()[$individuKey]['price']
                ]; 
            }
            
        }
        return $ret;
    }

    function calculateFitnessValue($individu){
        return array_sum(array_column($this -> selectingItem($individu),'selectedPrice'));
       
    }

    function countSelectedItem($individu){
        return count($this->selectingItem($individu));
    }

    function searchBestIndividu($fits,$maxItem,$numberOfIndividuMaxItem){
        if($numberOfIndividuMaxItem === 1){
            $index = array_search($maxItem, array_column($fits,'numberOfSelectedItem'));
            return $fits[$index];
            echo'<br>';
        }
        else{ 
            foreach($fits as $key => $val){
                if($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] =[
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']

                    ];
                }
            }
            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1);
            }
            else{
                $max = max(array_column($ret,'fitnessValue'));
                $index = array_search($max,array_column($ret,'fitnessValue'));
            }
            echo '<br>Hasil: ';
            // print_r($ret[$index]);
            return $ret[$index]; 
        }
    }

    function isFound($fits){
        $countedMaxItem = array_count_values(array_column($fits,'numberOfSelectedItem'));
        print_r($countedMaxItem);
        echo '<br>';
        $maxItem = max(array_keys($countedMaxItem));
        echo $maxItem;
        echo '<br>';
        echo $countedMaxItem[$maxItem];
        echo '<br>';
        $numberOfIndividuMaxItem = $countedMaxItem[$maxItem];

        $bestFitnessValue = $this -> searchBestIndividu($fits,$maxItem,$numberOfIndividuMaxItem)['fitnessValue'];
        //print_r($bestFitnessValue)['fitnessValue'];
        echo '<br>Best fitness value: '.$bestFitnessValue;
 
 
        $residual = Parameters::BUDGET - $bestFitnessValue;
        echo 'Residual: '. $residual;

        if($residual <= Parameters::STOPING_VALUE && $residual > 0){
            return True;
        }
    }

    function isFit($fitnessValue){
        if($fitnessValue <= Parameters::BUDGET){
            return True;
        }
    }

    function fitnessEvaluation($population){
        $catalogue = new Catalogue;
        foreach($population as $listOfindividuKey => $listOfIndividu){
            echo 'Individu-'. $listOfindividuKey. '<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp';
                print_r($catalogue -> product()[$individuKey]);
                echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu); 
            $numberOfSelectingItem = $this -> countSelectedItem($listOfIndividu);
            echo 'Max Item: '.$numberOfSelectingItem;
            echo  ' Fitness Value :'. $fitnessValue;
            if($this -> isFit($fitnessValue)){
                echo '(Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfindividuKey,
                    'numberOfSelectedItem' => $numberOfSelectingItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits); 
            }
            else{
                echo '(Not Fit)';
            }
           
            echo '<p>';
        }
        if($this -> isFound($fits)){
            echo ' Found';
        }
        else{
            echo'>> next generation';
        }
       
    }
}
$parameters = [
    'file_name' => 'produk.txt',
    'columns' => ['item', 'price'],
    'population_size' => 10
];


class Crossover{
    public $population;
    
    function __construct($population){
        $this -> population = $population;
    }

    function randomZerotoOne(){
        return (float) rand() / (float) getrandmax();
    }
 
    function generateCrossover(){
        for($i = 0;$i<= Parameters::POPULATION_SIZE-1;$i++){
            $randomZerotoOne = $this -> randomZerotoOne();
            if($randomZerotoOne < Parameters::CROSSOVERRATE){ 
                $parents[$i] = $randomZerotoOne;

            }
        }
        foreach (array_keys($parents) as $key) {
            foreach (array_keys($parents) as $subkey) {
                if($key !== $subkey){
                    $ret[] = [$key,$subkey];
                }
                
            }
            array_shift($parents);
        }
        return $ret;
    }

    function offspring($parents1,$parents2,$cutPointIndex,$offspring){
        $lengthOfgen = new Individu;

        if($offspring === 1){
            for ($i=0;$i<=$lengthOfgen->countNumberOfGen()-1;$i++){
                if($i <= $cutPointIndex){
                    $ret[] = $parents1[$i];
                }
                if($i > $cutPointIndex){
                    $ret[] = $parents2[$i];
                }
            }
            
        }

        if($offspring === 2){
            for ($i=0;$i<=$lengthOfgen->countNumberOfGen()-1;$i++){
                if($i <= $cutPointIndex){
                    $ret[] = $parents2[$i];
                }
                if($i > $cutPointIndex){
                    $ret[] = $parents1[$i];
                }
            }
           
        }
        return $ret;
    }

    function cutPointRandom(){
        $lengthOfgen = new Individu; 
        return rand(0,$lengthOfgen->countNumberOfGen()-1);
    }

    function crossover(){
        $cutPointIndex = $this->cutPointRandom();
        //echo"<br> Cut Point Index: ";
        // echo $cutPointIndex;
        foreach($this->generateCrossover() as $listCrossover){
            //proses mengambil parent yang telah terpilih
            $parents1 = $this -> population[$listCrossover[0]];
            $parents2 = $this -> population[$listCrossover[1]];
            // echo"<br><br>";
            // echo"Parents: <br>";
            // foreach($parents1 as $gen){
            //     echo $gen;//mengoutputkan parent 1
            // }
            // echo'><';
            // foreach($parents2 as $gen){
            //     echo $gen;//mengoutputkan parent 2
            // }
            // echo"<br>";
            // echo"Offspring Index: <br >";
            //proses crossover yang nanti akan menghasilkan 2 individu baru
            $offspring1 = $this->offspring($parents1,$parents2,$cutPointIndex,1);
            $offspring2 = $this->offspring($parents1,$parents2,$cutPointIndex,2);
            // foreach($offspring1 as $gen){
            //     echo $gen;//hasil crossover individu baru ke 1
            // }
            // echo'><';
            // foreach($offspring2  as $gen){

            //     echo $gen;//hasil crossover individu baru ke 2
            // }
            $offspring[]=$offspring1;
            $offspring[]=$offspring2;
            
        }
        return $offspring;
    } 

}

class Randomizer{
    static function getRandomIndexOfGen(){
        return rand(0,(new Individu())->countNumberOfGen()-1);
    }

    static function getRandomIndexOfIndividu(){
        return rand(0,Parameters::POPULATION_SIZE - 1);
    }
}

class Mutation{
    
    function __construct($population){
        $this->population = $population;
    }

    function calculateMutationRate(){
        return 1/(new Individu())->countNumberOfGen();
    }

    function calculateNumOfMutation(){
        return round($this -> calculateMutationRate() * Parameters::POPULATION_SIZE);
    }

    function isMutation(){ 
        if($this->calculateNumOfMutation()>0){ 
            return TRUE;
        }
    }

    function generateMutation($valueOfGen){
        if($valueOfGen===0){ 
            return 1;
        }
        else{
            return 0;
        }
    }

    function mutation(){

        if($this->isMutation()){ 

            for($i=0 ; $i <= $this->calculateNumOfMutation()-1;$i++){
                
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
                $indexofGen = Randomizer::getRandomIndexOfGen(); 
                $selectedindividu = $this->population[$indexOfIndividu]; 

                echo"<br> Individu ke-";
                print_r($indexOfIndividu);
                echo"<br> Before Mutation: <br>";
                print_r($selectedindividu);

                echo"<br> Letak Gen yang dimutasi <br>";
                print_r($indexofGen);

                $valueOfGen = $selectedindividu[$indexofGen];
                $mutatedGen = $this->generateMutation($valueOfGen);
                $selectedindividu[$indexofGen] = $mutatedGen;

                echo"<br> After Mutation: <br>";
                print_r($selectedindividu);
                echo"<br>";

                $ret[] = $selectedindividu;
            }
            return $ret;
        }

    }

}


// $katalog = new Catalogue;
// // print_r($katalog -> product($parameters));
// $katalog -> product($parameters);

$initialPopulation = new Population;
$population = $initialPopulation -> createRandomPopulation();

//$fitness = new Fitness;
//$fitness -> fitnessEvaluation($population);

$crossover = new Crossover($population);
$crossoverOffspring= $crossover->crossover();

echo'Crossover Offsrping: <br>';
print_r($crossoverOffspring);

echo"<p></p>";

//(new Mutation($population))->mutation();
$mutation = new Mutation($population);
if($mutation->mutation()){

    $mutationOffSprings = $mutation->mutation();
    echo '<br><br>Mutation offspring <br>';
    print_r($mutationOffSprings);
    echo"<p></p>";
    foreach($mutationOffSprings as $mutationOffSprings){
        $crossoverOffspring[] = $mutationOffSprings;
    }
}

echo 'Mutation Offsprings <br>';
print_r($crossoverOffspring);
// $individu = new individu;
// print_r($individu -> createRandomIndividu());