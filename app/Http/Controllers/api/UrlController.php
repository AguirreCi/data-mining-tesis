<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;


use App\Url;

class UrlController extends Controller
{
    public function obtener_datos($url_get){

    	$url = base64_decode($url_get);

        $urls = Url::where('url',$url)->get();

        if (count($urls)>0){
            $id = $urls->first()->id;

        }else{

        $id = $this->insertar_url($url);


			$this->obtener_tags($id);
			$this->obtener_whois($id);
			$this->subdominios($id);
			$this->analizar_longitud($id);
			$this->guiones($id);
			$this->ranking($id);
			$this->ssl($id);
			$this->dias_desde_reg($id);

        }

			$respuesta = ['id'=>$id];

			return json_encode($respuesta);
	}

	public function insertar_url($url){
		
		//INSERTO URL
		DB::table('url')->insert(['url' => $url]);
		
		//Recupero el id
		$url = Url::orderBy('id','DESC')->first();

		$id = $url->id;
		return $id;


	}


    public function obtener_tags($id){

        
        $elemento = Url::find($id);

        $datos = $this->get_tags($elemento->url);

        $elemento->tags = $datos['meta'];
        $elemento->titulo = str_limit($datos['titulo'], $limit = 990, $end = '...');
        $elemento->save();


        return 1;

    }

    public function obtener_whois($id){



    	$elemento = Url::find($id);

        $domain =$this->dominio($elemento->url);
        $whois = $this->get_whois($domain);

        $elemento->whois = $whois["whois"];
        $fecha =  strlen($whois["fecha"])>0?date('Y-m-d',strtotime(str_replace('.','-',str_replace('/', '-', $whois["fecha"])))):date('Y-m-d H:i:s');

		$elemento->fec_reg = $fecha;


        $elemento->save();


        return 1;

    }


    public function get_html($url){


        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($c,CURLOPT_TIMEOUT,600);
        //curl_setopt(... other options you want...)

        $html = curl_exec($c);

        // Get the status code
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        curl_close($c);

        if($status == 200){
            $respuesta = $html;
        }else{
            $respuesta = "Sitio no disponible";
        }

        return $respuesta;

    }


    public function get_whois($url){


        $c = curl_init("https://whois-app-cin.herokuapp.com/api/whois/".$url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($c,CURLOPT_TIMEOUT,600);
        //curl_setopt(... other options you want...)

        $json = curl_exec($c);

        // Get the status code
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        curl_close($c);


        $obj = json_decode($json);

        $whois_answer = str_replace(" ", "", isset($obj->Raw)?$obj->Raw:"");

  
        $fecha = "";
        $tiene = 0;

        if (strpos($whois_answer, "CreationDate:")!== false) {
            $fecha_str = substr($whois_answer, strpos($whois_answer, "CreationDate:")+13);
            $fecha = substr($fecha_str,0, strpos($fecha_str, "\r\n"));
            $fecha = substr($fecha,0, (strpos($fecha, "T")!==null)?strpos($fecha,'T'):strlen($fecha));
            $tiene = 1;
        }elseif (strpos($whois_answer, "created:\t")!== false) {
            $fecha_str = substr($whois_answer, strpos($whois_answer, "created:\t")+9);
            $fecha = substr($fecha_str,0, strpos($fecha_str, "\n"));
            $fecha = substr($fecha,0, (strpos($fecha, "T")!==null)?strpos($fecha,'T'):strlen($fecha));
            $tiene = 1;
        }elseif (strpos($whois_answer, "Registeredon:")!== false) {
            $fecha_str = substr($whois_answer, strpos($whois_answer, "Registeredon:")+13);
            $fecha = substr($fecha_str,0, strpos($fecha_str, "\r"));
            $fecha = substr($fecha,0, (strpos($fecha, "T")!==null)?strpos($fecha,'T'):strlen($fecha));
            $tiene = 1;
            
        }elseif (strpos($whois_answer, "created:")!== false) {
            $fecha_str = substr($whois_answer, strpos($whois_answer, "created:")+8);
            $fecha = substr($fecha_str,0, strpos($fecha_str, "\n"));
            $fecha = substr($fecha,0, (strpos($fecha, "T")!==null)?strpos($fecha,'T'):strlen($fecha));
            $fecha = substr($fecha,0, (strpos($fecha,'(')!==null)?strpos($fecha,'('):strlen($fecha));
            $tiene = 1;
        }elseif (condition) {
            # code...
        }


        $resultado = new Collection();

        $resultado = collect(['fecha'=>$fecha,'whois'=>$tiene]);

        return $resultado;
    }


    public function get_tags($url){

        $dom = $this->html_doc($url);

        $result = new Collection;

        $meta = $dom->getElementsByTagName('meta');
        $cantidadtags = 0;
        foreach ($meta as $tag) {
            $cantidadtags++;
        }


        $title = $dom->getElementsByTagName('title');

        $resultado = "SIN TITULO";

        foreach ($title as $tag) {
             $resultado = $tag->nodeValue;
             break;
        }

        $result->put("meta",$cantidadtags);
        $result->put("titulo",$resultado);

        return $result;
    }


    public function html_doc($url){

        $html = $this->get_html($url);

        $dom = New  \DOMDocument();
        @$dom->loadHTML(utf8_encode($html));

        return $dom;
    }



    public function subdominios($id){


$tlds_paises = [
    'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bl', 'bm', 'bn', 'bo', 'bq', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cw', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mf', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'ss', 'st', 'su', 'sv', 'sx', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'yt', 'za', 'zm', 'zw'

];


$tlds_comunes = [
'aaa', 'aarp', 'abarth', 'abbott', 'abbvie', 'abc', 'able', 'abogado', 'abudhabi', 'academy', 'accenture', 'accountant', 'accountants', 'aco', 'active', 'actor', 'adac', 'ads', 'adult', 'aeg', 'aero', 'aetna', 'afamilycompany', 'afl', 'africa', 'agakhan', 'agency', 'aig', 'aigo', 'airbus', 'airforce', 'airtel', 'akdn', 'alfaromeo', 'alibaba', 'alipay', 'allfinanz', 'allstate', 'ally', 'alsace', 'alstom', 'americanexpress', 'americanfamily', 'amex', 'amfam', 'amica', 'amsterdam', 'analytics', 'android', 'anquan', 'anz', 'aol', 'apartments', 'app', 'apple', 'aquarelle', 'arab', 'aramco', 'archi', 'army', 'arpa', 'art', 'arte', 'asda', 'asia', 'associates', 'athleta', 'attorney', 'auction', 'audi', 'audible', 'audio', 'auspost', 'author', 'auto', 'autos', 'avianca', 'aws', 'axa', 'azure', 'baby', 'baidu', 'banamex', 'bananarepublic', 'band', 'bank', 'bar', 'barcelona', 'barclaycard', 'barclays', 'barefoot', 'bargains', 'baseball', 'basketball', 'bauhaus', 'bayern', 'bbc', 'bbt', 'bbva', 'bcg', 'bcn', 'beats', 'beauty', 'beer', 'bentley', 'berlin', 'best', 'bestbuy', 'bet', 'bharti', 'bible', 'bid', 'bike', 'bing', 'bingo', 'bio', 'biz', 'black', 'blackfriday', 'blanco', 'blockbuster', 'blog', 'bloomberg', 'blue', 'bms', 'bmw', 'bnl', 'bnpparibas', 'boats', 'boehringer', 'bofa', 'bom', 'bond', 'boo', 'book', 'booking', 'bosch', 'bostik', 'boston', 'bot', 'boutique', 'box', 'bradesco', 'bridgestone', 'broadway', 'broker', 'brother', 'brussels', 'budapest', 'bugatti', 'build', 'builders', 'business', 'buy', 'buzz', 'bzh', 'cab', 'cafe', 'cal', 'call', 'calvinklein', 'cam', 'camera', 'camp', 'cancerresearch', 'canon', 'capetown', 'capital', 'capitalone', 'car', 'caravan', 'cards', 'care', 'career', 'careers', 'cars', 'cartier', 'casa', 'case', 'caseih', 'cash', 'casino', 'cat', 'catering', 'catholic', 'cba', 'cbn', 'cbre', 'cbs', 'ceb', 'center', 'ceo', 'cern', 'cfa', 'cfd', 'chanel', 'channel', 'charity', 'chase', 'chat', 'cheap', 'chintai', 'christmas', 'chrome', 'chrysler', 'church', 'cipriani', 'circle', 'cisco', 'citadel', 'citi', 'citic', 'city', 'cityeats', 'claims', 'cleaning', 'click', 'clinic', 'clinique', 'clothing', 'cloud', 'club', 'clubmed', 'coach', 'codes', 'coffee', 'college', 'cologne', 'com','co', 'comcast', 'commbank', 'community', 'company', 'compare', 'computer', 'comsec', 'condos', 'construction', 'consulting', 'contact', 'contractors', 'cooking', 'cookingchannel', 'cool', 'coop', 'corsica', 'country', 'coupon', 'coupons', 'courses', 'credit', 'creditcard', 'creditunion', 'cricket', 'crown', 'crs', 'cruise', 'cruises', 'csc', 'cuisinella', 'cymru', 'cyou', 'dabur', 'dad', 'dance', 'data', 'date', 'dating', 'datsun', 'day', 'dclk', 'dds', 'deal', 'dealer', 'deals', 'degree', 'delivery', 'dell', 'deloitte', 'delta', 'democrat', 'dental', 'dentist', 'desi', 'design', 'dev', 'dhl', 'diamonds', 'diet', 'digital', 'direct', 'directory', 'discount', 'discover', 'dish', 'diy', 'dnp', 'docs', 'doctor', 'dodge', 'dog', 'doha', 'domains', 'dot', 'download', 'drive', 'dtv', 'dubai', 'duck', 'dunlop', 'duns', 'dupont', 'durban', 'dvag', 'dvr', 'earth', 'eat', 'eco', 'edeka', 'edu', 'education', 'email', 'emerck', 'energy', 'engineer', 'engineering', 'enterprises', 'epost', 'epson', 'equipment', 'ericsson', 'erni', 'esq', 'estate', 'esurance', 'etisalat', 'eurovision', 'eus', 'events', 'everbank', 'exchange', 'expert', 'exposed', 'express', 'extraspace', 'fage', 'fail', 'fairwinds', 'faith', 'family', 'fan', 'fans', 'farm', 'farmers', 'fashion', 'fast', 'fedex', 'feedback', 'ferrari', 'ferrero', 'fiat', 'fidelity', 'fido', 'film', 'final', 'finance', 'financial', 'fire', 'firestone', 'firmdale', 'fish', 'fishing', 'fit', 'fitness', 'flickr', 'flights', 'flir', 'florist', 'flowers', 'fly', 'foo', 'food', 'foodnetwork', 'football', 'ford', 'forex', 'forsale', 'forum', 'foundation', 'fox', 'free', 'fresenius', 'frl', 'frogans', 'frontdoor', 'frontier', 'ftr', 'fujitsu', 'fujixerox', 'fun', 'fund', 'furniture', 'futbol', 'fyi', 'gal', 'gallery', 'gallo', 'gallup', 'game', 'games', 'gap', 'garden', 'gbiz', 'gdn', 'gea', 'gent', 'genting', 'george', 'ggee', 'gift', 'gifts', 'gives', 'giving', 'glade', 'glass', 'gle', 'global', 'globo', 'gmail', 'gmbh', 'gmo', 'gmx', 'godaddy', 'gold', 'goldpoint', 'golf', 'goo', 'goodhands', 'goodyear', 'goog', 'google', 'gop', 'got', 'gov', 'grainger', 'graphics', 'gratis', 'green', 'gripe', 'grocery', 'group', 'guardian', 'gucci', 'guge', 'guide', 'guitars', 'guru', 'hair', 'hamburg', 'hangout', 'haus', 'hbo', 'hdfc', 'hdfcbank', 'health', 'healthcare', 'help', 'helsinki', 'here', 'hermes', 'hgtv', 'hiphop', 'hisamitsu', 'hitachi', 'hiv', 'hkt', 'hockey', 'holdings', 'holiday', 'homedepot', 'homegoods', 'homes', 'homesense', 'honda', 'honeywell', 'horse', 'hospital', 'host', 'hosting', 'hot', 'hoteles', 'hotels', 'hotmail', 'house', 'how', 'hsbc', 'hughes', 'hyatt', 'hyundai', 'ibm', 'icbc', 'ice', 'icu', 'ieee', 'ifm', 'ikano', 'imamat', 'imdb', 'immo', 'immobilien', 'inc', 'industries', 'infiniti', 'info', 'ing', 'ink', 'institute', 'insurance', 'insure', 'int', 'intel', 'international', 'intuit', 'investments', 'ipiranga', 'irish', 'iselect', 'ismaili', 'ist', 'istanbul', 'itau', 'itv', 'iveco', 'jaguar', 'java', 'jcb', 'jcp', 'jeep', 'jetzt', 'jewelry', 'jio', 'jlc', 'jll', 'jmp', 'jnj', 'jobs', 'joburg', 'jot', 'joy', 'jpmorgan', 'jprs', 'juegos', 'juniper', 'kaufen', 'kddi', 'kerryhotels', 'kerrylogistics', 'kerryproperties', 'kfh', 'kia', 'kim', 'kinder', 'kindle', 'kitchen', 'kiwi', 'koeln', 'komatsu', 'kosher', 'kpmg', 'kpn', 'krd', 'kred', 'kuokgroup', 'kyoto', 'lacaixa', 'ladbrokes', 'lamborghini', 'lamer', 'lancaster', 'lancia', 'lancome', 'land', 'landrover', 'lanxess', 'lasalle', 'lat', 'latino', 'latrobe', 'law', 'lawyer', 'lds', 'lease', 'leclerc', 'lefrak', 'legal', 'lego', 'lexus', 'lgbt', 'liaison', 'lidl', 'life', 'lifeinsurance', 'lifestyle', 'lighting', 'like', 'lilly', 'limited', 'limo', 'lincoln', 'linde', 'link', 'lipsy', 'live', 'living', 'lixil', 'llc', 'loan', 'loans', 'locker', 'locus', 'loft', 'lol', 'london', 'lotte', 'lotto', 'love', 'lpl', 'lplfinancial', 'ltd', 'ltda', 'lundbeck', 'lupin', 'luxe', 'luxury', 'macys', 'madrid', 'maif', 'maison', 'makeup', 'man', 'management', 'mango', 'map', 'market', 'marketing', 'markets', 'marriott', 'marshalls', 'maserati', 'mattel', 'mba', 'mckinsey', 'med', 'media', 'meet', 'melbourne', 'meme', 'memorial', 'men', 'menu', 'merckmsd', 'metlife', 'miami', 'microsoft', 'mil', 'mini', 'mint', 'mit', 'mitsubishi', 'mlb', 'mls', 'mma', 'mobi', 'mobile', 'mobily', 'moda', 'moe', 'moi', 'mom', 'monash', 'money', 'monster', 'mopar', 'mormon', 'mortgage', 'moscow', 'moto', 'motorcycles', 'mov', 'movie', 'movistar', 'msd', 'mtn', 'mtr', 'museum', 'mutual', 'nab', 'nadex', 'nagoya', 'name', 'nationwide', 'natura', 'navy', 'nba', 'nec', 'net', 'netbank', 'netflix', 'network', 'neustar', 'new', 'newholland', 'news', 'next', 'nextdirect', 'nexus', 'nfl', 'ngo', 'nhk', 'nico', 'nike', 'nikon', 'ninja', 'nissan', 'nissay', 'nokia', 'northwesternmutual', 'norton', 'now', 'nowruz', 'nowtv', 'nra', 'nrw', 'ntt', 'nyc', 'obi', 'observer', 'off', 'office', 'okinawa', 'olayan', 'olayangroup', 'oldnavy', 'ollo', 'omega', 'one', 'ong', 'onl', 'online', 'onyourside', 'ooo', 'open', 'oracle', 'orange', 'org', 'organic', 'origins', 'osaka', 'otsuka', 'ott', 'ovh', 'page', 'panasonic', 'panerai', 'paris', 'pars', 'partners', 'parts', 'party', 'passagens', 'pay', 'pccw', 'pet', 'pfizer', 'pharmacy', 'phd', 'philips', 'phone', 'photo', 'photography', 'photos', 'physio', 'piaget', 'pics', 'pictet', 'pictures', 'pid', 'pin', 'ping', 'pink', 'pioneer', 'pizza', 'place', 'play', 'playstation', 'plumbing', 'plus', 'pnc', 'pohl', 'poker', 'politie', 'porn', 'post', 'pramerica', 'praxi', 'press', 'prime', 'pro', 'prod', 'productions', 'prof', 'progressive', 'promo', 'properties', 'property', 'protection', 'pru', 'prudential', 'pub', 'pwc', 'qpon', 'quebec', 'quest', 'qvc', 'racing', 'radio', 'raid', 'read', 'realestate', 'realtor', 'realty', 'recipes', 'red', 'redstone', 'redumbrella', 'rehab', 'reise', 'reisen', 'reit', 'reliance', 'ren', 'rent', 'rentals', 'repair', 'report', 'republican', 'rest', 'restaurant', 'review', 'reviews', 'rexroth', 'rich', 'richardli', 'ricoh', 'rightathome', 'ril', 'rio', 'rip', 'rmit', 'rocher', 'rocks', 'rodeo', 'rogers', 'room', 'rsvp', 'rugby', 'ruhr', 'run', 'rwe', 'ryukyu', 'saarland', 'safe', 'safety', 'sakura', 'sale', 'salon', 'samsclub', 'samsung', 'sandvik', 'sandvikcoromant', 'sanofi', 'sap', 'sarl', 'sas', 'save', 'saxo', 'sbi', 'sbs', 'sca', 'scb', 'schaeffler', 'schmidt', 'scholarships', 'school', 'schule', 'schwarz', 'science', 'scjohnson', 'scor', 'scot', 'search', 'seat', 'secure', 'security', 'seek', 'select', 'sener', 'services', 'ses', 'seven', 'sew', 'sex', 'sexy', 'sfr', 'shangrila', 'sharp', 'shaw', 'shell', 'shia', 'shiksha', 'shoes', 'shop', 'shopping', 'shouji', 'show', 'showtime', 'shriram', 'silk', 'sina', 'singles', 'site', 'ski', 'skin', 'sky', 'skype', 'sling', 'smart', 'smile', 'sncf', 'soccer', 'social', 'softbank', 'software', 'sohu', 'solar', 'solutions', 'song', 'sony', 'soy', 'space', 'spiegel', 'sport', 'spot', 'spreadbetting', 'srl', 'srt', 'stada', 'staples', 'star', 'starhub', 'statebank', 'statefarm', 'statoil', 'stc', 'stcgroup', 'stockholm', 'storage', 'store', 'stream', 'studio', 'study', 'style', 'sucks', 'supplies', 'supply', 'support', 'surf', 'surgery', 'suzuki', 'swatch', 'swiftcover', 'swiss', 'sydney', 'symantec', 'systems', 'tab', 'taipei', 'talk', 'taobao', 'target', 'tatamotors', 'tatar', 'tattoo', 'tax', 'taxi', 'tci', 'tdk', 'team', 'tech', 'technology', 'tel', 'telefonica', 'temasek', 'tennis', 'teva', 'thd', 'theater', 'theatre', 'tiaa', 'tickets', 'tienda', 'tiffany', 'tips', 'tires', 'tirol', 'tjmaxx', 'tjx', 'tkmaxx', 'tmall', 'today', 'tokyo', 'tools', 'top', 'toray', 'toshiba', 'total', 'tours', 'town', 'toyota', 'toys', 'trade', 'trading', 'training', 'travel', 'travelchannel', 'travelers', 'travelersinsurance', 'trust', 'trv', 'tube', 'tui', 'tunes', 'tushu', 'tvs', 'ubank', 'ubs', 'uconnect', 'unicom', 'university', 'uno', 'uol', 'ups', 'vacations', 'vana', 'vanguard', 'vegas', 'ventures', 'verisign', 'versicherung', 'vet', 'viajes', 'video', 'vig', 'viking', 'villas', 'vin', 'vip', 'virgin', 'visa', 'vision', 'vista', 'vistaprint', 'viva', 'vivo', 'vlaanderen', 'vodka', 'volkswagen', 'volvo', 'vote', 'voting', 'voto', 'voyage', 'vuelos', 'wales', 'walmart', 'walter', 'wang', 'wanggou', 'warman', 'watch', 'watches', 'weather', 'weatherchannel', 'webcam', 'weber', 'website', 'wed', 'wedding', 'weibo', 'weir', 'whoswho', 'wien', 'wiki', 'williamhill', 'win', 'windows', 'wine', 'winners', 'wme', 'wolterskluwer', 'woodside', 'work', 'works', 'world', 'wow', 'wtc', 'wtf', 'xbox', 'xerox', 'xfinity', 'xihuan', 'xin', 'xxx', 'xyz', 'yachts', 'yahoo', 'yamaxun', 'yandex', 'ye', 'yodobashi', 'yoga', 'yokohama', 'you', 'youtube', 'yun', 'zappos', 'zara', 'zero', 'zip', 'zippo', 'zone', 'zuerich'
];


        $elemento = Url::find($id);


        $domain =parse_url($elemento->url);
        $target = array_key_exists("host",$domain)?$domain["host"]:$domain["path"];
        preg_match('#[^\.]+[\.]{1}[^\.]+$#', $target , $matches);
        $value = $matches[0]; 
        $lista = explode('.', $target);
        $largo = count($lista)-1;
        $pais = 0;
        $tldnormal = 0;
        $dominio = 0;
        $contador = 1;
        $tlds = "";
        $subdominios = 0;
        while ( $largo>= 0) {
            if ($pais==0) {
                if (in_array($lista[$largo],$tlds_paises)) {
                    $pais = 1;
                    $tlds = ".".$lista[$largo];
                }elseif (in_array($lista[$largo],$tlds_comunes)) {
                    $pais = 1;
                    $tldnormal = 1;
                    $tlds = ".".$lista[$largo].$tlds;
                }
            }else{                        
                if ($tldnormal == 0) {
                    if (in_array($lista[$largo],$tlds_comunes)) {
                        $pais = 1;
                        $tldnormal = 1;
                        $tlds = ".".$lista[$largo].$tlds;
                    }elseif ($contador>1) {
                        $tldnormal = 1;
                        $dominio = 1;
                    }
                }else{
                    if ($dominio==0) {
                        $dominio = 1;
                    }else{
                        $subdominios++;
                    }
                }
            }
            $largo--;
            $contador++;
        }
        
        $elemento->tld = $tlds;
        $elemento->subdominios = $subdominios;
        $elemento->save();


        return 1;
 

    }



    public function analizar_longitud($id){
        

        $elemento = Url::find($id);

        $url_string = $elemento->url;
        $titulo_string = $elemento->titulo;

        $long_url = strlen($url_string);

        $long_titulo = strlen($titulo_string);

        $digitos = 0;

        for ($i=0; $i < $long_url; $i++) { 
            if (in_array($url_string[$i], ['0','1','2','3','4','5','6','7','8','9'])) {
                $digitos++;
            }
        }

        

        $elemento->largo_titulo = $long_titulo;
        $elemento->largo = $long_url;
        $elemento->numeros = $digitos;
        $elemento->save();


        return 1;
    }



    public function guiones($id){

    	$elemento = Url::find($id);

        $domain =parse_url($elemento->url);
        $target = array_key_exists("host",$domain)?$domain["host"]:$domain["path"];

        $long_dominio = strlen($target);

        $guiones = 0;

        for ($i=0; $i < $long_dominio; $i++) { 
            if (in_array($target[$i], ['-','_'])) {
                $guiones++;
            }
        }

        
        $elemento->guiones = $guiones;
        $elemento->save();

        return 'listo!';


        
    }


    public function ranking($id){

        $url = Url::find($id);

        $cuenta =0;

        $urls = array($url);

        while ( $cuenta < count($urls)) {
            

            $lista = array();
            $resultados = new Collection;

            $urls_buenas = $urls;

            foreach ($urls_buenas as $url_base) {

                    $domain =parse_url($url_base->url);
                    $target = array_key_exists("host",$domain)?$domain["host"]:$domain["path"];
                    preg_match('#[^\.]+[\.]{1}[^\.]+$#', $target , $matches);

                    $value = $matches[0]; 


                    $item = new Collection;


                    $item->put('id',$url_base->id);
                    $item->put('dominio',$value);


                    $lista[] = $value;

                    $resultados->push($item);


                }

                    $url = 'https://openpagerank.com/api/v1.0/getPageRank';
                    $query = http_build_query(array(
                            'domains' => $lista
                        ));
                    $url = $url .'?'. $query;
                    $ch = curl_init();
                    $headers = ['API-OPR: coswg4g888cos00wck0sc0gggs8gkwsk8g0s0kw8'];
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec ($ch);
                    curl_close ($ch);
                    $output = json_decode($output,true);


                    $respuesta = $output['response'];


                    for ($i=0; $i < count($respuesta); $i++) { 
                        $ranking = is_numeric($respuesta[$i]['rank'])?$respuesta[$i]['rank']:0;
                        $id = $resultados[$i]['id'];
                        $elemento = Url::find($id);
                        $elemento->rank = $ranking;
                        $elemento->save();

                    }



                    $cuenta +=100;

        }


        return 1;

    }


    public function ssl($id){

        $elemento = Url::find($id);

        $ssl = 0;
        $domain =parse_url($elemento->url);

        $esquema = array_key_exists("scheme",$domain)?$domain["scheme"]:'';

        if (strlen($esquema)<1) {

            $url_armada = "http://".$url->url;

            $ssl = $this->url_tiene_https($url_armada)?1:0;

        }else{
            $ssl= strpos($esquema,'https')?1:0;
        }

        
        $elemento->https = $ssl;
        $elemento->save();

        return 1;


    }


    public function url_tiene_https($url){

        $result = false;
        $stream = stream_context_create (array("ssl" => array("capture_peer_cert" => true,"verify_peer" => false, "verify_peer_name" => false)));
        try {
        
        if (@fopen(str_replace('http://','https://', $url), "rb", false, $stream)) {
            $read = fopen(str_replace('http://','https://', $url), "rb", false, $stream);
            $context = stream_context_get_params($read);
            $cert = ($context["options"]["ssl"]["peer_certificate"]);
            $result = (!is_null($cert)) ? true : false;
            
        }else{
            throw new Exception('exepcion');
        }
            
        } catch (Exception $e) {
            
        }

        

        return $result;
    }


    public function dias_desde_reg($id){

    	$elemento = Url::find($id);

        $now = strtotime(date('Y-m-d',time()));
        $your_date = strtotime($elemento->fec_reg);
        $datediff = $now - $your_date;

        $cant_dias =  round($datediff / (60 * 60 * 24));
        
        $elemento->dias_reg = $cant_dias;
        $elemento->save();

    }




public function dominio($url){

        
     



$tlds_paises = [
    'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bl', 'bm', 'bn', 'bo', 'bq', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cw', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mf', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'ss', 'st', 'su', 'sv', 'sx', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'yt', 'za', 'zm', 'zw'

];


$tlds_comunes = [
'aaa', 'aarp', 'abarth', 'abbott', 'abbvie', 'abc', 'able', 'abogado', 'abudhabi', 'academy', 'accenture', 'accountant', 'accountants', 'aco', 'active', 'actor', 'adac', 'ads', 'adult', 'aeg', 'aero', 'aetna', 'afamilycompany', 'afl', 'africa', 'agakhan', 'agency', 'aig', 'aigo', 'airbus', 'airforce', 'airtel', 'akdn', 'alfaromeo', 'alibaba', 'alipay', 'allfinanz', 'allstate', 'ally', 'alsace', 'alstom', 'americanexpress', 'americanfamily', 'amex', 'amfam', 'amica', 'amsterdam', 'analytics', 'android', 'anquan', 'anz', 'aol', 'apartments', 'app', 'apple', 'aquarelle', 'arab', 'aramco', 'archi', 'army', 'arpa', 'art', 'arte', 'asda', 'asia', 'associates', 'athleta', 'attorney', 'auction', 'audi', 'audible', 'audio', 'auspost', 'author', 'auto', 'autos', 'avianca', 'aws', 'axa', 'azure', 'baby', 'baidu', 'banamex', 'bananarepublic', 'band', 'bank', 'bar', 'barcelona', 'barclaycard', 'barclays', 'barefoot', 'bargains', 'baseball', 'basketball', 'bauhaus', 'bayern', 'bbc', 'bbt', 'bbva', 'bcg', 'bcn', 'beats', 'beauty', 'beer', 'bentley', 'berlin', 'best', 'bestbuy', 'bet', 'bharti', 'bible', 'bid', 'bike', 'bing', 'bingo', 'bio', 'biz', 'black', 'blackfriday', 'blanco', 'blockbuster', 'blog', 'bloomberg', 'blue', 'bms', 'bmw', 'bnl', 'bnpparibas', 'boats', 'boehringer', 'bofa', 'bom', 'bond', 'boo', 'book', 'booking', 'bosch', 'bostik', 'boston', 'bot', 'boutique', 'box', 'bradesco', 'bridgestone', 'broadway', 'broker', 'brother', 'brussels', 'budapest', 'bugatti', 'build', 'builders', 'business', 'buy', 'buzz', 'bzh', 'cab', 'cafe', 'cal', 'call', 'calvinklein', 'cam', 'camera', 'camp', 'cancerresearch', 'canon', 'capetown', 'capital', 'capitalone', 'car', 'caravan', 'cards', 'care', 'career', 'careers', 'cars', 'cartier', 'casa', 'case', 'caseih', 'cash', 'casino', 'cat', 'catering', 'catholic', 'cba', 'cbn', 'cbre', 'cbs', 'ceb', 'center', 'ceo', 'cern', 'cfa', 'cfd', 'chanel', 'channel', 'charity', 'chase', 'chat', 'cheap', 'chintai', 'christmas', 'chrome', 'chrysler', 'church', 'cipriani', 'circle', 'cisco', 'citadel', 'citi', 'citic', 'city', 'cityeats', 'claims', 'cleaning', 'click', 'clinic', 'clinique', 'clothing', 'cloud', 'club', 'clubmed', 'coach', 'codes', 'coffee', 'college', 'cologne', 'com','co', 'comcast', 'commbank', 'community', 'company', 'compare', 'computer', 'comsec', 'condos', 'construction', 'consulting', 'contact', 'contractors', 'cooking', 'cookingchannel', 'cool', 'coop', 'corsica', 'country', 'coupon', 'coupons', 'courses', 'credit', 'creditcard', 'creditunion', 'cricket', 'crown', 'crs', 'cruise', 'cruises', 'csc', 'cuisinella', 'cymru', 'cyou', 'dabur', 'dad', 'dance', 'data', 'date', 'dating', 'datsun', 'day', 'dclk', 'dds', 'deal', 'dealer', 'deals', 'degree', 'delivery', 'dell', 'deloitte', 'delta', 'democrat', 'dental', 'dentist', 'desi', 'design', 'dev', 'dhl', 'diamonds', 'diet', 'digital', 'direct', 'directory', 'discount', 'discover', 'dish', 'diy', 'dnp', 'docs', 'doctor', 'dodge', 'dog', 'doha', 'domains', 'dot', 'download', 'drive', 'dtv', 'dubai', 'duck', 'dunlop', 'duns', 'dupont', 'durban', 'dvag', 'dvr', 'earth', 'eat', 'eco', 'edeka', 'edu', 'education', 'email', 'emerck', 'energy', 'engineer', 'engineering', 'enterprises', 'epost', 'epson', 'equipment', 'ericsson', 'erni', 'esq', 'estate', 'esurance', 'etisalat', 'eurovision', 'eus', 'events', 'everbank', 'exchange', 'expert', 'exposed', 'express', 'extraspace', 'fage', 'fail', 'fairwinds', 'faith', 'family', 'fan', 'fans', 'farm', 'farmers', 'fashion', 'fast', 'fedex', 'feedback', 'ferrari', 'ferrero', 'fiat', 'fidelity', 'fido', 'film', 'final', 'finance', 'financial', 'fire', 'firestone', 'firmdale', 'fish', 'fishing', 'fit', 'fitness', 'flickr', 'flights', 'flir', 'florist', 'flowers', 'fly', 'foo', 'food', 'foodnetwork', 'football', 'ford', 'forex', 'forsale', 'forum', 'foundation', 'fox', 'free', 'fresenius', 'frl', 'frogans', 'frontdoor', 'frontier', 'ftr', 'fujitsu', 'fujixerox', 'fun', 'fund', 'furniture', 'futbol', 'fyi', 'gal', 'gallery', 'gallo', 'gallup', 'game', 'games', 'gap', 'garden', 'gbiz', 'gdn', 'gea', 'gent', 'genting', 'george', 'ggee', 'gift', 'gifts', 'gives', 'giving', 'glade', 'glass', 'gle', 'global', 'globo', 'gmail', 'gmbh', 'gmo', 'gmx', 'godaddy', 'gold', 'goldpoint', 'golf', 'goo', 'goodhands', 'goodyear', 'goog', 'google', 'gop', 'got', 'gov', 'grainger', 'graphics', 'gratis', 'green', 'gripe', 'grocery', 'group', 'guardian', 'gucci', 'guge', 'guide', 'guitars', 'guru', 'hair', 'hamburg', 'hangout', 'haus', 'hbo', 'hdfc', 'hdfcbank', 'health', 'healthcare', 'help', 'helsinki', 'here', 'hermes', 'hgtv', 'hiphop', 'hisamitsu', 'hitachi', 'hiv', 'hkt', 'hockey', 'holdings', 'holiday', 'homedepot', 'homegoods', 'homes', 'homesense', 'honda', 'honeywell', 'horse', 'hospital', 'host', 'hosting', 'hot', 'hoteles', 'hotels', 'hotmail', 'house', 'how', 'hsbc', 'hughes', 'hyatt', 'hyundai', 'ibm', 'icbc', 'ice', 'icu', 'ieee', 'ifm', 'ikano', 'imamat', 'imdb', 'immo', 'immobilien', 'inc', 'industries', 'infiniti', 'info', 'ing', 'ink', 'institute', 'insurance', 'insure', 'int', 'intel', 'international', 'intuit', 'investments', 'ipiranga', 'irish', 'iselect', 'ismaili', 'ist', 'istanbul', 'itau', 'itv', 'iveco', 'jaguar', 'java', 'jcb', 'jcp', 'jeep', 'jetzt', 'jewelry', 'jio', 'jlc', 'jll', 'jmp', 'jnj', 'jobs', 'joburg', 'jot', 'joy', 'jpmorgan', 'jprs', 'juegos', 'juniper', 'kaufen', 'kddi', 'kerryhotels', 'kerrylogistics', 'kerryproperties', 'kfh', 'kia', 'kim', 'kinder', 'kindle', 'kitchen', 'kiwi', 'koeln', 'komatsu', 'kosher', 'kpmg', 'kpn', 'krd', 'kred', 'kuokgroup', 'kyoto', 'lacaixa', 'ladbrokes', 'lamborghini', 'lamer', 'lancaster', 'lancia', 'lancome', 'land', 'landrover', 'lanxess', 'lasalle', 'lat', 'latino', 'latrobe', 'law', 'lawyer', 'lds', 'lease', 'leclerc', 'lefrak', 'legal', 'lego', 'lexus', 'lgbt', 'liaison', 'lidl', 'life', 'lifeinsurance', 'lifestyle', 'lighting', 'like', 'lilly', 'limited', 'limo', 'lincoln', 'linde', 'link', 'lipsy', 'live', 'living', 'lixil', 'llc', 'loan', 'loans', 'locker', 'locus', 'loft', 'lol', 'london', 'lotte', 'lotto', 'love', 'lpl', 'lplfinancial', 'ltd', 'ltda', 'lundbeck', 'lupin', 'luxe', 'luxury', 'macys', 'madrid', 'maif', 'maison', 'makeup', 'man', 'management', 'mango', 'map', 'market', 'marketing', 'markets', 'marriott', 'marshalls', 'maserati', 'mattel', 'mba', 'mckinsey', 'med', 'media', 'meet', 'melbourne', 'meme', 'memorial', 'men', 'menu', 'merckmsd', 'metlife', 'miami', 'microsoft', 'mil', 'mini', 'mint', 'mit', 'mitsubishi', 'mlb', 'mls', 'mma', 'mobi', 'mobile', 'mobily', 'moda', 'moe', 'moi', 'mom', 'monash', 'money', 'monster', 'mopar', 'mormon', 'mortgage', 'moscow', 'moto', 'motorcycles', 'mov', 'movie', 'movistar', 'msd', 'mtn', 'mtr', 'museum', 'mutual', 'nab', 'nadex', 'nagoya', 'name', 'nationwide', 'natura', 'navy', 'nba', 'nec', 'net', 'netbank', 'netflix', 'network', 'neustar', 'new', 'newholland', 'news', 'next', 'nextdirect', 'nexus', 'nfl', 'ngo', 'nhk', 'nico', 'nike', 'nikon', 'ninja', 'nissan', 'nissay', 'nokia', 'northwesternmutual', 'norton', 'now', 'nowruz', 'nowtv', 'nra', 'nrw', 'ntt', 'nyc', 'obi', 'observer', 'off', 'office', 'okinawa', 'olayan', 'olayangroup', 'oldnavy', 'ollo', 'omega', 'one', 'ong', 'onl', 'online', 'onyourside', 'ooo', 'open', 'oracle', 'orange', 'org', 'organic', 'origins', 'osaka', 'otsuka', 'ott', 'ovh', 'page', 'panasonic', 'panerai', 'paris', 'pars', 'partners', 'parts', 'party', 'passagens', 'pay', 'pccw', 'pet', 'pfizer', 'pharmacy', 'phd', 'philips', 'phone', 'photo', 'photography', 'photos', 'physio', 'piaget', 'pics', 'pictet', 'pictures', 'pid', 'pin', 'ping', 'pink', 'pioneer', 'pizza', 'place', 'play', 'playstation', 'plumbing', 'plus', 'pnc', 'pohl', 'poker', 'politie', 'porn', 'post', 'pramerica', 'praxi', 'press', 'prime', 'pro', 'prod', 'productions', 'prof', 'progressive', 'promo', 'properties', 'property', 'protection', 'pru', 'prudential', 'pub', 'pwc', 'qpon', 'quebec', 'quest', 'qvc', 'racing', 'radio', 'raid', 'read', 'realestate', 'realtor', 'realty', 'recipes', 'red', 'redstone', 'redumbrella', 'rehab', 'reise', 'reisen', 'reit', 'reliance', 'ren', 'rent', 'rentals', 'repair', 'report', 'republican', 'rest', 'restaurant', 'review', 'reviews', 'rexroth', 'rich', 'richardli', 'ricoh', 'rightathome', 'ril', 'rio', 'rip', 'rmit', 'rocher', 'rocks', 'rodeo', 'rogers', 'room', 'rsvp', 'rugby', 'ruhr', 'run', 'rwe', 'ryukyu', 'saarland', 'safe', 'safety', 'sakura', 'sale', 'salon', 'samsclub', 'samsung', 'sandvik', 'sandvikcoromant', 'sanofi', 'sap', 'sarl', 'sas', 'save', 'saxo', 'sbi', 'sbs', 'sca', 'scb', 'schaeffler', 'schmidt', 'scholarships', 'school', 'schule', 'schwarz', 'science', 'scjohnson', 'scor', 'scot', 'search', 'seat', 'secure', 'security', 'seek', 'select', 'sener', 'services', 'ses', 'seven', 'sew', 'sex', 'sexy', 'sfr', 'shangrila', 'sharp', 'shaw', 'shell', 'shia', 'shiksha', 'shoes', 'shop', 'shopping', 'shouji', 'show', 'showtime', 'shriram', 'silk', 'sina', 'singles', 'site', 'ski', 'skin', 'sky', 'skype', 'sling', 'smart', 'smile', 'sncf', 'soccer', 'social', 'softbank', 'software', 'sohu', 'solar', 'solutions', 'song', 'sony', 'soy', 'space', 'spiegel', 'sport', 'spot', 'spreadbetting', 'srl', 'srt', 'stada', 'staples', 'star', 'starhub', 'statebank', 'statefarm', 'statoil', 'stc', 'stcgroup', 'stockholm', 'storage', 'store', 'stream', 'studio', 'study', 'style', 'sucks', 'supplies', 'supply', 'support', 'surf', 'surgery', 'suzuki', 'swatch', 'swiftcover', 'swiss', 'sydney', 'symantec', 'systems', 'tab', 'taipei', 'talk', 'taobao', 'target', 'tatamotors', 'tatar', 'tattoo', 'tax', 'taxi', 'tci', 'tdk', 'team', 'tech', 'technology', 'tel', 'telefonica', 'temasek', 'tennis', 'teva', 'thd', 'theater', 'theatre', 'tiaa', 'tickets', 'tienda', 'tiffany', 'tips', 'tires', 'tirol', 'tjmaxx', 'tjx', 'tkmaxx', 'tmall', 'today', 'tokyo', 'tools', 'top', 'toray', 'toshiba', 'total', 'tours', 'town', 'toyota', 'toys', 'trade', 'trading', 'training', 'travel', 'travelchannel', 'travelers', 'travelersinsurance', 'trust', 'trv', 'tube', 'tui', 'tunes', 'tushu', 'tvs', 'ubank', 'ubs', 'uconnect', 'unicom', 'university', 'uno', 'uol', 'ups', 'vacations', 'vana', 'vanguard', 'vegas', 'ventures', 'verisign', 'versicherung', 'vet', 'viajes', 'video', 'vig', 'viking', 'villas', 'vin', 'vip', 'virgin', 'visa', 'vision', 'vista', 'vistaprint', 'viva', 'vivo', 'vlaanderen', 'vodka', 'volkswagen', 'volvo', 'vote', 'voting', 'voto', 'voyage', 'vuelos', 'wales', 'walmart', 'walter', 'wang', 'wanggou', 'warman', 'watch', 'watches', 'weather', 'weatherchannel', 'webcam', 'weber', 'website', 'wed', 'wedding', 'weibo', 'weir', 'whoswho', 'wien', 'wiki', 'williamhill', 'win', 'windows', 'wine', 'winners', 'wme', 'wolterskluwer', 'woodside', 'work', 'works', 'world', 'wow', 'wtc', 'wtf', 'xbox', 'xerox', 'xfinity', 'xihuan', 'xin', 'xxx', 'xyz', 'yachts', 'yahoo', 'yamaxun', 'yandex', 'ye', 'yodobashi', 'yoga', 'yokohama', 'you', 'youtube', 'yun', 'zappos', 'zara', 'zero', 'zip', 'zippo', 'zone', 'zuerich'
];


    $domain =parse_url($url);

    $target = array_key_exists("host",$domain)?$domain["host"]:$domain["path"];

    $lista = explode('.', $target);

    $largo = count($lista)-1;

    $pais = 0;
    $tldnormal = 0;
    $dominio = 0;
    $contador = 1;

    $respuesta = "";


    $tlds = "";

    $subdominios = 0;

    while ( $largo>= 0) {
        if ($pais==0) {
            if (in_array($lista[$largo],$tlds_paises)) {
                $pais = 1;
                $tlds = ".".$lista[$largo];
            }elseif (in_array($lista[$largo],$tlds_comunes)) {
                $pais = 1;
                $tldnormal = 1;
                $tlds = ".".$lista[$largo].$tlds;
            }
        }else{
                        
            if ($tldnormal == 0) {
                if (in_array($lista[$largo],$tlds_comunes)) {
                    $pais = 1;
                    $tldnormal = 1;
                    $tlds = ".".$lista[$largo].$tlds;
                }elseif ($contador>1) {
                        $tldnormal = 1;
                        $respuesta = $lista[$largo].$tlds;
                        $dominio = 1;
                }

            }else{
                if ($dominio==0) {
                    $respuesta = $lista[$largo].$tlds;
                    $dominio = 1;
                }else{
                    $subdominios++;
                }
            }
        }
        $largo--;
        $contador++;
    }


    return $respuesta;

    }






}
