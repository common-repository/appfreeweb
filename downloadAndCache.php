<?
/**
 * Descarga y cachea por un tiempo una url
 * @author Sati
 *
  */
class DownloadAndCache{

	private static $tmp_dir = 'wp-content/uploads/';
	private static $prefix = 'appfreeweb-cache';
	/**
	 * Descarga una URL y la cachea
	 *
	 * @param string $URL
	 * @param int $lifetime
	 * @return string
	 */
	public static function descargar($URL, $lifetime = null){
		$file_hash = self::getFile($URL, $lifetime);
		if (!file_exists($file_hash)) {
			$data=self::descargar_por_socket($URL);
			if(!$data)
				$data=self::descargar_por_curl($URL);
			if(!$data)
				$data=self::descargar_normal($URL);
			if(!$data || strlen($data)<5){
				return false;
			}else{
				$fp = @fopen($file_hash, 'wb+');
				@fwrite($fp, $data);
				@fclose($fp);
			}
		} else{
			$data = '';
			$fp = fopen($file_hash, 'rb');
			while (!feof($fp)) {
				$data.=fread($fp, 1024);
			}
			fclose($fp);
		}
		return $data;
	}

	private static function getLifeTime($lifetime = null){
		$lifetime = intval($lifetime);
		if ($lifetime>0) {
			$lifetime = time()+$lifetime;
		} else {
			$lifetime = 999999999;
		}
		return $lifetime;
	}
	private static function clean(){
		$files = glob(self::$tmp_dir.self::$prefix.'*.tmp');
		$now = time();
		if(is_array($files))
		foreach ($files as $file)
		{
			if (preg_match("/.*?\/".self::$prefix."(.*?)_(.*?)\.tmp/is", $file, $r)) {
				if ($r[2]<$now) {
					unlink($file);
				}
			}
		}
	}
	private static function getFile($URL, $lifetime){
		self::clean();
		$hash = crc32($URL);
		$lifetime = self::getLifeTime($lifetime);
		$base = self::$tmp_dir.self::$prefix.$hash;
		$file_hash = $base.'_*.tmp';
		$file = glob($file_hash);
		if (!empty($file)) {
			$file_hash = array_shift($file);
		}else{
			$file_hash = $base.'_'.$lifetime.'.tmp';
		}
		return $file_hash;
	}
	private static function descargar_por_curl($URL) {

		$ch = curl_init ($URL);

	   curl_setopt ($ch, CURLOPT_URL, $URL);
	   curl_setopt ($ch, CURLOPT_HEADER, 0);
	   curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		return curl_exec ($ch);
		curl_close ($ch);

	}
	private static function descargar_normal($URL){
		return file_get_contents($URL);
	}
	private static function descargar_por_socket($URL){
		$regs=parse_url($URL);
		$da = fsockopen($regs['host'], ($regs['port']?$regs['port']:80), $errno, $errstr, 5);
		if ($da) {
		    $salida = "GET {$regs['path']}".($regs['query']?'?'.$regs['query']:'')." HTTP/1.0\r\n";
		    $salida .= "Host: {$regs['host']}\r\n";
		    if ($regs['user']) {
		    	$tmp = $regs['user'];
		    	if (!empty($regs['pass'])) {
			    	$tmp .= ':'.$regs['pass'];
		    	}
		    	$salida.= "Authorization: Basic " . base64_encode($tmp) . "\r\n";
		    }
		    $salida .= "Connection: Close\r\n\r\n";
			$ch='';
		    fwrite($da, $salida);
		    while (!feof($da)) {
		        $ch.=fgets($da, 128);
		    }
		    fclose($da);
		    list($header, $content)=explode("\r\n\r\n", $ch, 2);
		    if(!empty($content)){
		    	return $content;
		    }
		}
		return false;
	}
	public static function setTmp($tmp = ''){
		if (file_exists($tmp) && is_dir($tmp) && is_writable($tmp)) {
			self::$tmp_dir = $tmp;
			return true;
		}
		return false;
	}
	public static function setPrefix($prefix = 'du_'){
		if (!empty($prefix)) {
			self::$prefix = $prefix;
			return true;
		}
		return false;
	}

}


?>