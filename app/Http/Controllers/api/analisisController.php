<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use Phpml\Classification\NaiveBayes;
use Phpml\Classification\DecisionTree;
use Phpml\ModelManager;



use App\Url;


class analisisController extends Controller
{


	public function analizar_url($id){
		$res_naive = $this->analisis_naive($id);

		$res_tree = $this->analisis_tree($id);


		$rta = ['tree'=>$res_tree, 'naive'=>$res_naive];

		return json_encode($rta);

	}

    public function analisis_naive($id){
    	$naiveClassifier = new NaiveBayes();


    	$modelManager = new ModelManager();
		$naiveClassifier = $modelManager->restoreFromFile('modelos/naive.txt');

		$url = Url::find($id);

		$item = [intval($url->tags),intval($url->whois),intval($url->https),intval($url->dias_reg),intval($url->largo_titulo),intval($url->numeros),intval($url->largo),intval($url->rank),intval($url->subdominios),intval($url->guiones)];

		$result = $naiveClassifier->predict($item);

		return $result;
    }

	public function analisis_tree($id){
    	$naiveClassifier = new DecisionTree();


    	$modelManager = new ModelManager();
		$treeClassifier = $modelManager->restoreFromFile('modelos/tree.txt');

		$url = Url::find($id);

		$item = [intval($url->tags),intval($url->whois),intval($url->https),intval($url->dias_reg),intval($url->largo_titulo),intval($url->numeros),intval($url->largo),intval($url->rank),intval($url->subdominios),intval($url->guiones)];

		$result = $treeClassifier->predict($item);

		return $result;
    }



    public function analisis_virusTotal($id){
    	$elemento = Url::find($id);

		header("Content-Type: text/plain"); 


		$virustotal_api_key = "f3e20f3fdae81d9cea753ed13f8f491ce974ce35a763a629a2ff2be8136c01a6";


		$file_hash = $elemento->url;

		$report_url = 'https://www.virustotal.com/vtapi/v2/url/report?apikey='.$virustotal_api_key."&resource=".$file_hash;

		$api_reply = file_get_contents($report_url);



		$api_reply_array = json_decode($api_reply,true);



if($api_reply_array['response_code']==-2){
	$rta= $api_reply_array['verbose_msg'];

}

if($api_reply_array['response_code']==1){
	$rta= "\nHay ".$api_reply_array['positives']." informes positivos de phishing.\n\n";
}

if($api_reply_array['response_code']=='0'){

	$rta= "\nNo se encontraron datos sobre esta URL";
}



return json_encode(["resultado"=>$rta]);

    }


}
