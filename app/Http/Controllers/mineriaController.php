<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Math\Distance\Minkowski;


use Phpml\Metric\Accuracy;
use Phpml\Metric\ConfusionMatrix;
use Phpml\Metric\ClassificationReport;

use Phpml\Dataset\ArrayDataset;


use Phpml\CrossValidation\RandomSplit;


use Phpml\ModelManager;

use App\Url;

class mineriaController extends Controller
{
    //

    public function armado_datasets(){

    	//ARMO LOS SAMPLES

    	$samples = array();
    	$labels = array();

    	$urls_buenas = Url::where('tipo',0)->get();
    	$urls_phishing = Url::where('tipo',1)->get();


    	foreach ($urls_buenas as $url) {

    		$item = [intval($url->tags),intval($url->whois),intval($url->https),intval($url->dias_reg),intval($url->largo_titulo),intval($url->numeros),intval($url->largo),intval($url->rank),intval($url->subdominios),intval($url->guiones)];
    		
    		$samples[] = $item;

    		$labels[] = $url->tipo;
    		
    	}

    	foreach ($urls_phishing as $url) {

    		$item = [intval($url->tags),intval($url->whois),intval($url->https),intval($url->dias_reg),intval($url->largo_titulo),intval($url->numeros),intval($url->largo),intval($url->rank),intval($url->subdominios),intval($url->guiones)];
    		
    		$samples[] = $item;

    		$labels[] = $url->tipo;
    	}


    	$dataset = new ArrayDataset($samples,$labels);


    	$randomSplit = new RandomSplit($dataset, 0.3);


		// entrenamiento
		$prueba_data = $randomSplit->getTrainSamples();
		$prueba_labels = $randomSplit->getTrainLabels();

		// test
		$test_data = $randomSplit->getTestSamples();
		$test_labels = $randomSplit->getTestLabels();


    	//ENTRENAMIENTO
		file_put_contents('prueba_data.txt', json_encode($prueba_data));
		file_put_contents('prueba_labels.txt', json_encode($prueba_labels));

		//TEST
		file_put_contents('test_data.txt', json_encode($test_data));
		file_put_contents('test_labels.txt', json_encode($test_labels));

		return "LISTO LOS DATOS";

    }

    public function naivebayes_proc(){

    	//ARMO LOS SAMPLES

    	//ENTRENAMIENTO
    	$samples = json_decode(file_get_contents('prueba_data.txt'));
    	$labels = json_decode(file_get_contents('prueba_labels.txt'));

    	//PRUEBA
    	$samples_pru = json_decode(file_get_contents('test_data.txt'));
    	$labels_pru = json_decode(file_get_contents('test_labels.txt'));

		$classifier = new NaiveBayes();
		$classifier->train($samples, $labels);

		$modelManager = new ModelManager();
		$modelManager->saveToFile($classifier, 'modelos/naive.txt');

		$result = $classifier->predict($samples_pru);

		file_put_contents('naive_data.txt', json_encode($result));
		
		return "Listo naive";

	}
	
	public function svm_proc(){

    	//ARMO LOS SAMPLES

    	//ENTRENAMIENTO
    	$samples = json_decode(file_get_contents('prueba_data.txt'));
    	$labels = json_decode(file_get_contents('prueba_labels.txt'));

    	//PRUEBA
    	$samples_pru = json_decode(file_get_contents('test_data.txt'));
    	$labels_pru = json_decode(file_get_contents('test_labels.txt'));

		$classifier = new SVC(Kernel::RBF, $cost = 1000);
		$classifier->train($samples, $labels);

		$modelManager = new ModelManager();
		$modelManager->saveToFile($classifier, 'modelos/svm.txt');

		$result = $classifier->predict($samples_pru);

		file_put_contents('svm_data.txt', json_encode($result));
		
		return "Listo svm";

    }
		
	public function k_proc(){
		set_time_limit ( 15000 );
    	//ARMO LOS SAMPLES

    	//ENTRENAMIENTO
    	$samples = json_decode(file_get_contents('prueba_data.txt'));
    	$labels = json_decode(file_get_contents('prueba_labels.txt'));

    	//PRUEBA
    	$samples_pru = json_decode(file_get_contents('test_data.txt'));
    	$labels_pru = json_decode(file_get_contents('test_labels.txt'));

		$classifier = new KNearestNeighbors($k=3, new Minkowski($lambda=4));
		$classifier->train($samples, $labels);

		$modelManager = new ModelManager();
		$modelManager->saveToFile($classifier, 'modelos/kn.txt');

		$result = $classifier->predict($samples_pru);

		file_put_contents('kn_data.txt', json_encode($result));
		
		return "Listo kn";

    }

	public function accuracy_svm(){

    	//ARMO LOS SAMPLES

    	//REAL
    	$labels = json_decode(file_get_contents('test_labels.txt'));

    	//PREDICCION
    	$result = json_decode(file_get_contents('svm_data.txt'));

		$nivel_accuracy = Accuracy::score($labels, $result);
		
		$confusionMatrix = ConfusionMatrix::compute($labels, $result);

		$report = new ClassificationReport($labels, $result);
		
		$precision_pred = $report->getPrecision();

		$sensibilidad = $report->getRecall();

		$f1 = $report->getF1score();

		$cantidad = $report->getSupport();

		$resultados = ['modelo'=>'SVM', 'precision'=>$nivel_accuracy,'matriz'=>$confusionMatrix,'sensibilidad'=>$sensibilidad, 'precision_pred'=>$precision_pred, 'f1'=>$f1, 'cant'=>$cantidad];

		
		return view('resultados')->with('resultados',$resultados);

    }
	
	public function accuracy_kn(){

    	//ARMO LOS SAMPLES

    	//REAL
    	$labels = json_decode(file_get_contents('test_labels.txt'));

    	//PREDICCION
    	$result = json_decode(file_get_contents('kn_data.txt'));

		$nivel_accuracy = Accuracy::score($labels, $result);
		
		$confusionMatrix = ConfusionMatrix::compute($labels, $result);

		$report = new ClassificationReport($labels, $result);
		
		$precision_pred = $report->getPrecision();

		$sensibilidad = $report->getRecall();

		$f1 = $report->getF1score();

		$cantidad = $report->getSupport();

		$resultados = ['modelo'=>'K Nearest Neighbors', 'precision'=>$nivel_accuracy,'matriz'=>$confusionMatrix,'sensibilidad'=>$sensibilidad, 'precision_pred'=>$precision_pred, 'f1'=>$f1, 'cant'=>$cantidad];

		
		return view('resultados')->with('resultados',$resultados);

    }

    public function accuracy_naive(){

    	//ARMO LOS SAMPLES

    	//REAL
    	$labels = json_decode(file_get_contents('test_labels.txt'));

    	//PREDICCION
    	$result = json_decode(file_get_contents('naive_data.txt'));

		$nivel_accuracy = Accuracy::score($labels, $result);

		$confusionMatrix = ConfusionMatrix::compute($labels, $result);
		
		$report = new ClassificationReport($labels, $result);
		
		$precision_pred = $report->getPrecision();

		$sensibilidad = $report->getRecall();

		$f1 = $report->getF1score();

		$cantidad = $report->getSupport();

		$resultados = ['modelo'=>'Naive Bayes', 'precision'=>$nivel_accuracy,'matriz'=>$confusionMatrix,'sensibilidad'=>$sensibilidad, 'precision_pred'=>$precision_pred, 'f1'=>$f1, 'cant'=>$cantidad];
		
		return view('resultados')->with('resultados',$resultados);

    }    
}
