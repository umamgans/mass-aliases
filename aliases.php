<?php

// coded by s4dness | Vengeful Ghost
// php file.php cp=http://cpanel user=user pass=pass list=list.txt

namespace Umam {
	class AliasesDomain {
		public $host, $user, $pass, $lists;
		public $color = [];
		
		function __construct() {
			$this->color["r"] = "\033[0;31m";
			$this->color["g"] = "\033[0;32m";
			$this->color["w"] = "\033[0m";
			$this->cp = @$_GET["cp"];
			$this->user = @$_GET["user"];
			$this->pass = @$_GET["pass"];
			$this->lists = explode("\n", file_get_contents(@$_GET["list"]));
			$this->aliases();
		}
		
		function aliases() {
			$login = $this->curl($this->cp . ":2082/login", [
				"user" => $this->user,
				"pass" => $this->pass
			]);
			if($login["head"] == 200) {
				echo "{$this->color["g"]}  [ info ] loged in cpanel{$this->color["w"]}\n";
				preg_match("/\/(.*?)\/(.*?)\/(.*?)\//", $login["body"], $path);
				foreach($this->lists as $u) {
					if(!preg_match("/error|Error/", $this->curl($this->cp . ":2082/" . $path[0] . "/park/doaddparked.html", [
						"domain" => $u,
						"go"	     => "Add Domain"
					])["body"])) {
						echo "{$this->color['g']} [+] $u ~> aliased{$this->color['w']}\n";
						system("echo '$u' >> aliased.txt");
					} else {
						echo "{$this->color['r']} [-] $u ~> error{$this->color['w']}\n";
					}
				} echo "\n{$this->color["g"]} [ info ] project complited{$this->color["w"]}\n\n";
			} else echo "{$this->color["r"]}  [ info ] failed login to cpanel{$this->color["w"]}\n";
		}
		
		function curl($url, $post){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 17);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_COOKIEJAR, "session_log");
			curl_setopt($ch, CURLOPT_COOKIEFILE, "session_log");
			$x = curl_exec($ch);
			return [
				"head" => curl_getinfo($ch, CURLINFO_HTTP_CODE),
				"body" => $x
			];
		}
	}
	parse_str(implode("&", array_slice($argv, 1)), $_GET);
	if(empty(@$_GET["cp"]) || empty(@$_GET["user"]) || 
	empty(@$_GET["pass"]) || empty(@$_GET["list"])) {
		echo "\033[0;34m [!] usage: php $argv[0] cp=http://cpanel user=user pass=pass list=list.txt\n";
		echo " [!] note: you must delete http/s from list.\033[0m\n";
		die;
	} elseif(!file_exists(@$_GET["list"])) {
		die("\033[0;34m [!] list not found\033[0m\n");
	} new \Umam\AliasesDomain();
}