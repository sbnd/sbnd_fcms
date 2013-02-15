<?php
/**
* SBND F&CMS - Framework & CMS for PHP developers
*
* Copyright (C) 1999 - 2013, SBND Technologies Ltd, Sofia, info@sbnd.net, http://sbnd.net
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author SBND Techologies Ltd <info@sbnd.net>
* @package basic.spam
* @version 7.0.4
*/


/**
 * Antispam image generator.
 *
 * @author Evgeni Baldziyski
 * @version 3.0 
 * @since 11.06.2007
 */
class BasicAntiSpam{
	/**
	 * 
	 * TTF image
	 * 
	 * @var string
	 * @access public
	 */
	public $ttf = 'alger.ttf';/*'infroman.ttf';*/
	/**
	 * 
	 * TTF path to the file
	 * @var string
	 * @access public
	 */
	public $ttf_path = '';
	/**
	 * 
	 * Width of the image
	 * @var int
	 * @access public
	 */
	public $width = 110;
	/**
	 * 
	 * Height of the image
	 * @var int
	 * @access public
	 */
	public $height = 30;
	/**
	 * 
	 * Length of the code
	 * @var int
	 * @access public
	 */
	public $lenght = 6;
	/**
	 * 
	 * Generator mode
	 *  1 - letters only
	 *  2 - letters and digits
	 *  3 - mathematic
	 * 
	 * @var int
	 * @access public
	 */
	public $mode = 2;
	/**
	 * 
	 * Mime type
	 * @var string
	 * @access public
	 */
	public $mime = 'png';
	/**
	 * 
	 * The size of generated text
	 * 
	 * @var int
	 * @access public
	 */
	public $text_size = 17;
	/**
	 * 
	 * Background color
	 * @var string
	 * @access public
	 */
	public $bg_color = '#F1F1F1';
	/**
	 * 
	 * Text color
	 * @access public
	 * @var string
	 */
	public $text_color = '#6F6F6F';
	/**
	 * 
	 * Line color
	 * @var string
	 * @access public
	 */
	public $line_color = '#D7D7D7';
	/**
	 * 
	 * Noise color
	 * @var string
	 * @access public
	 */
	public $noise_color = '#D7D7D7';
	/**
	 * 
	 * Number of lines
	 * @var int
	 * @access public
	 */
	public $num_lines = 5;
	/**
	 * 
	 * Noise level
	 * @var int
	 * @access public
	 */
	public $noise_level = 3;
	/**
	 * 
	 * @todo description
	 * @var int
	 * @access public
	 */
	public $iscale = 5;
	/**
	 * 
	 * Transperancy level
	 * @var int
	 * @access public
	 */
	public $transparency = 50;
	
	/**
	 * 
	 * code
	 * @var string
	 * @access private
	 */
	protected $code = '';
	/**
	 * 
	 * System image flag
	 * @var boolean
	 */
	protected $system_image = false;
	/**
	 * 
	 * Background color
	 * @var string
	 * @access private
	 */
	protected $bgcolor;
	/**
	 * 
	 * Text color
	 * @var string
	 * @access private
	 */
	protected $textcolor;
	/**
	 * 
	 * LIne color
	 * @var string
	 * @access private
	 */
	protected $linecolor;
	/**
	 * 
	 * Noise color
	 * @var string
	 * @access private
	 */
	protected $noisecolor;
	/**
	 * 
	 * Image object
	 * @var object
	 * @access private
	 */
	protected $im;
	/**
	 * 
	 * Code that will be displayed
	 * @var string
	 * @access private
	 */
	protected $code_display = '';
	/**
	 * Construct
	 *
	 * @param string/array $arr
	 * @return antiSpam
	 */
	function __construct($arr = array()){
		foreach($arr as $k => $v){
			$this->$k = $v;
		}
		if(!extension_loaded('gd')){
			if (!function_exists("dl")){
				die("Function 'dl' not supported!");
			}
			/**
			 * developer's tool
			 */
			if (!@dl("php_gd2.dll") && !@dl("php_gd2.so")) {
				$this->code = '123456';
				$this->system_image = true;
			}
		}
		if(!$this->ttf_path){
			$this->ttf_path = BASIC::init()->ini_get('root_path').BASIC::init()->ini_get('basic_path').'scripts/ttf/';
		}
	}
	/**
	 * Get generate code
	 *
	 * @return string
	 */
	function getCode(){
		if(!$this->code){
			switch ($this->mode) {
				case 1: //Letters only
					for($i = 0; $i < $this->lenght; $i++){
						$this->code .= chr(rand(66, 90));
					}
					$this->code_display = $this->code;
					break;
				case 2: //Letters and digits
					for ($i=0; $i < $this->lenght; $i++) {
						$this->code .= chr(rand(0,1)==1 ? rand(66, 90) : rand(48, 57));
					}
					$this->code_display = $this->code;
					break;
				case 3: //Mathematic
	                $signs = array('+', '-', 'x');
	                $left  = rand(1, 10);
	                $right = rand(1, 5);
	                $sign  = $signs[rand(0, 2)];
	                
	                switch($sign) {
	                    case 'x': $c = $left * $right; break;
	                    case '-': $c = $left - $right; break;
	                    default:  $c = $left + $right; break;
	                }
	                
	                $this->code         = $c;
	                $this->code_display = $left." ".$sign." ".$right;
					break;
				default: //DEFAULT: Digits only
					$this->code = $this->code_display = rand(pow(10, $this->lenght-1), pow(10, $this->lenght));
					break;
			}
		}
		return $this->code;
	}
	/**
	 * Image Creator
	 *
	 */
	function getImage(){
		/**
		 * developer's tool
		 */
	    if($this->system_image){
	        header("Content-type: image/jpeg");
	        print base64_decode(
	           '/9j/4AAQSkZJRgABAgEASABIAAD/4QivRXhpZgAATU0AKgAAAAgABwESAAMAAAABAAEAAAEaAAUAAAABAAAAYgEbAAUA'.
	           'AAABAAAAagEoAAMAAAABAAIAAAExAAIAAAAcAAAAcgEyAAIAAAAUAAAAjodpAAQAAAABAAAApAAAANAACvyAAAAnEAAK'.
	           '/IAAACcQQWRvYmUgUGhvdG9zaG9wIENTMyBXaW5kb3dzADIwMDk6MDE6MDMgMDA6MDM6MzkAAAAAA6ABAAMAAAABAAEA'.
	           'AKACAAQAAAABAAAAeKADAAQAAAABAAAAIwAAAAAAAAAGAQMAAwAAAAEABgAAARoABQAAAAEAAAEeARsABQAAAAEAAAEm'.
	           'ASgAAwAAAAEAAgAAAgEABAAAAAEAAAEuAgIABAAAAAEAAAd5AAAAAAAAAEgAAAABAAAASAAAAAH/2P/gABBKRklGAAEC'.
	           'AABIAEgAAP/tAAxBZG9iZV9DTQAB/+4ADkFkb2JlAGSAAAAAAf/bAIQADAgICAkIDAkJDBELCgsRFQ8MDA8VGBMTFRMT'.
	           'GBEMDAwMDAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAENCwsNDg0QDg4QFA4ODhQUDg4ODhQRDAwMDAwREQwM'.
	           'DAwMDBEMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM/8AAEQgAIwB4AwEiAAIRAQMRAf/dAAQACP/EAT8AAAEFAQEB'.
	           'AQEBAAAAAAAAAAMAAQIEBQYHCAkKCwEAAQUBAQEBAQEAAAAAAAAAAQACAwQFBgcICQoLEAABBAEDAgQCBQcGCAUDDDMB'.
	           'AAIRAwQhEjEFQVFhEyJxgTIGFJGhsUIjJBVSwWIzNHKC0UMHJZJT8OHxY3M1FqKygyZEk1RkRcKjdDYX0lXiZfKzhMPT'.
	           'dePzRieUpIW0lcTU5PSltcXV5fVWZnaGlqa2xtbm9jdHV2d3h5ent8fX5/cRAAICAQIEBAMEBQYHBwYFNQEAAhEDITES'.
	           'BEFRYXEiEwUygZEUobFCI8FS0fAzJGLhcoKSQ1MVY3M08SUGFqKygwcmNcLSRJNUoxdkRVU2dGXi8rOEw9N14/NGlKSF'.
	           'tJXE1OT0pbXF1eX1VmZ2hpamtsbW5vYnN0dXZ3eHl6e3x//aAAwDAQACEQMRAD8A9VSSXCZHXPrXT9aui5HVcT9mdLy7'.
	           'X4f2VuQLt77Gu9Cy702tr3ept2JKe7VHqdXWLfSr6ZdTitJcb77WG1wiPTbVjzWyzf7vU33V+n/wivLC+tJ646vHxumY'.
	           'lmTj3Od9tdRZVVa1g27Kq3ZVlLf0+5++xn0ElJvq91DLy2ZlWVazJOFkGhmZU3Yy4bKrnObWHWtb6Nlr8Z+y1/vpWus/'.
	           'obrvsLarOnO6S2g+nVjOfVZ7ABD2uxbLmf53vVvJqdfj20tsfS6xjmC2uA9hcNvqV7g5u9n5vtSUzD2FxYHAubG5oOon'.
	           '6MhSXAda+rbOgdT6Z1LpHVMjGzsm3HwbqXk5Lspjdtb3mux30qqW+pZu/QV/4P0V36SlLlvrr9ZusdDt6ZjdIxaszK6n'.
	           'a+lldxLfcNmwNdvqY3d6n57lq9G+sfTOt35tXT3OsGBY2qy0gBjnObv/AEXu3+z6D97K/eua/wAYmTRi9c+quTk2Nqoq'.
	           'zXPsseYa1o9Hc5zklLftv/Gz/wDO7if9v1/+9i3fqxnfW3L+0/8AOTp1PTtnp/ZvRsbZvn1PW3endkbfT21f56l/z2+q'.
	           'P/lvif8Abrf71rY2TRlUV5ONY22i1ofXYwy1zT9FzXJKSrIyfrd9WMW+zGyeqY1V9Tiyyt9jQ5rh9JrmrD+uvVsmnr/R'.
	           'eiOuFPTOq+qzO1DHFrdm3bke2yj/AK29E/b31L6d+o/Zrbvs36P1fslt+7bpv+1elZ9o/wCN9T3pKepxMzFzsZmVh2tv'.
	           'x7RNdrDua4A7fa4fygkq/Seo9MzcWt3Ty1jC0ubRt9N7RMHfjO22Ve795iSSn//Q9VXL/wCMjGst+q12XT/P9MtqzqT4'.
	           'OpeNx/7afYuoWH9Zej9a6xWMLC6izAwLq315jTQ26x7XjbsZ6rtjGuZuakp1sPKrzMSjLq/m8itlrP6r2h7f+qRlz31e'.
	           '+pmN0Oym0Z+bmWUV+jW2+0mprIjZVjMDa2NXQpKQZedh4NbLMy5lDLHtqY6whoL3nbXWJ/Oc5Zn1vwK87oOQ2zqFnSm0'.
	           'RkHLqMbTV+kb6kQ59e78xjt+9Q+t/SerdS6fS7o91VOfhXtyqPWY14c5gdFYc8O9Fzt384syjE+sf1nb09n1gw29P6dT'.
	           'N2dih+52RfW/ZRU+v8zD9v2nZus9VJSf6p9Juyq8H6x9VyLczPfhsroFzBX6IdLrn11N/wAJk+zdc79J6aX156xXjUU9'.
	           'HvDqsXqzLa8rLFdlvp0gNZeytmMy1/2m1l36vY9j6WP/AJ2tdQnSU8P9ROp9Kt6/1zFwbLLGP+zOxzZVYwurpopxnOf6'.
	           'lVWx+/8AMf6e/wCnWz011PVehdI6w2tvVMWvLbSSaxYJ2l0bo/zVn9D+rvU+m9Yz+pZPU25bOokOtoGOKiHsDaqX+r61'.
	           'v0KWbNvp+9T+tGX1fp9VHU8O2tnTsEuu6rURutsoaGu9PF3N2+r/ADn0raP+MSUw/wCYP1N/8qcf/NP96yvqHmZTuu/W'.
	           'TpZsJwOl31U4GP8Am1Vzkt9Or+TtqrVP/wAev6q/9xc//tun/wB6lo/UTpeQzM6v9Yi5n2P6xPqysOuT6rGTe/bks2+m'.
	           'yz9YZ/NW3JKav186Wb+v9E6pmY7bui9P9V3UrLQ19bGHZHq0u3Osb/1t60KP8YP1Cx6WUY/UaaaawG11V12Na1o4axja'.
	           'trWrqUklPC/VLEycn679X+sdLC/o/UaQMPLEbbC00sdtYf0rfdVZ9NiS7pJJT//R9VSXyqkkp+qkl8qpJKfqpJfKqSSn'.
	           '6qSXyqkkp+qkl8qpJKfqpJfKqSSn6qSXyqkkp+qkl8qpJKf/2f/tDeRQaG90b3Nob3AgMy4wADhCSU0EJQAAAAAAEAAA'.
	           'AAAAAAAAAAAAAAAAAAA4QklNBC8AAAAAAEoY2wEASAAAAEgAAAAAAAAAAAAAANACAABAAgAAAAAAAAAAAAAYAwAAZAIA'.
	           'AAABwAMAALAEAAABAA8nAQBsbHVuAAAAAAAAAAAAADhCSU0D7QAAAAAAEABIAAAAAQABAEgAAAABAAE4QklNBCYAAAAA'.
	           'AA4AAAAAAAAAAAAAP4AAADhCSU0EDQAAAAAABAAAAHg4QklNBBkAAAAAAAQAAAAeOEJJTQPzAAAAAAAJAAAAAAAAAAAB'.
	           'ADhCSU0ECgAAAAAAAQAAOEJJTScQAAAAAAAKAAEAAAAAAAAAAjhCSU0D9QAAAAAASAAvZmYAAQBsZmYABgAAAAAAAQAv'.
	           'ZmYAAQChmZoABgAAAAAAAQAyAAAAAQBaAAAABgAAAAAAAQA1AAAAAQAtAAAABgAAAAAAAThCSU0D+AAAAAAAcAAA/////////////////////////////wPoAAAAAP////////////////////////////8D6AAAAAD/////////////////////////////A+gAAAAA/////////////////////////////wPoAAA4QklNBAAAAAAAAAIABjhCSU0EAgAAAAAADgAAAAAAAAAAAAAAAAAAOEJJTQQwAAAAAAAHAQEBAQEBAQA4QklNBC0AAAAAAAYAAQAAAAc4QklNBAgAAAAAABAAAAABAAACQAAAAkAAAAAAOEJJTQQeAAAAAAAEAAAAADhCSU0EGgAAAAADOwAAAAYAAAAAAAAAAAAAACMAAAB4AAAAAwAxADIAMAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAeAAAACMAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAQAAAAAAAG51bGwAAAACAAAABmJvdW5kc09iamMAAAABAAAAAAAAUmN0MQAAAAQAAAAAVG9wIGxvbmcAAAAAAAAAAExlZnRsb25nAAAAAAAAAABCdG9tbG9uZwAAACMAAAAAUmdodGxvbmcAAAB4AAAABnNsaWNlc1ZsTHMAAAABT2JqYwAAAAEAAAAAAAVzbGljZQAAABIAAAAHc2xpY2VJRGxvbmcAAAAAAAAAB2dyb3VwSURsb25nAAAAAAAAAAZvcmlnaW5lbnVtAAAADEVTbGljZU9yaWdpbgAAAA1hdXRvR2VuZXJhdGVkAAAAAFR5cGVlbnVtAAAACkVTbGljZVR5cGUAAAAASW1nIAAAAAZib3VuZHNPYmpjAAAAAQAAAAAAAFJjdDEAAAAEAAAAAFRvcCBsb25nAAAAAAAAAABMZWZ0bG9uZwAAAAAAAAAAQnRvbWxvbmcAAAAjAAAAAFJnaHRsb25nAAAAeAAAAAN1cmxURVhUAAAAAQAAAAAAAG51bGxURVhUAAAAAQAAAAAAAE1zZ2VURVhUAAAAAQAAAAAABmFsdFRhZ1RFWFQAAAABAAAAAAAOY2VsbFRleHRJc0hUTUxib29sAQAAAAhjZWxsVGV4dFRFWFQA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cD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eGFwTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIiB4bWxuczpleGlmPSJodHRwOi8vbnMuYWRvYmUuY29tL2V4aWYvMS4wLyIgZGM6Zm9ybWF0PSJpbWFnZS9qcGVnIiB4YXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzMgV2luZG93cyIgeGFwOkNyZWF0ZURhdGU9IjIwMDktMDEtMDNUMDA6MDM6MzkrMDI6MDAiIHhhcDpNb2RpZnlEYXRlPSIyMDA5LTAxLTAzVDAwOjAzOjM5KzAyOjAwIiB4YXA6TWV0YWRhdGFEYXRlPSIyMDA5LTAxLTAzVDAwOjAzOjM5KzAyOjAwIiB4YXBNTTpEb2N1bWVudElEPSJ1dWlkOkZGODI1NDkyMThEOUREMTFBQTE1OEYxRDk5QjZBNTkwIiB4YXBNTTpJbnN0YW5jZUlEPSJ1dWlkOjAwODM1NDkyMThEOUREMTFBQTE1OEYxRDk5QjZBNTkwIiBwaG90b3Nob3A6Q29sb3JNb2RlPSIzIiBwaG90b3Nob3A6SUNDUHJvZmlsZT0ic1JHQiBJRUM2MTk2Ni0yLjEiIHBob3Rvc2hvcDpIaXN0b3J5PSIiIHRpZmY6T3JpZW50YXRpb249IjEiIHRpZmY6WFJlc29sdXRpb249IjcyMDAwMC8xMDAwMCIgdGlmZjpZUmVzb2x1dGlvbj0iNzIwMDAwLzEwMDAwIiB0aWZmOlJlc29sdXRpb25Vbml0PSIyIiB0aWZmOk5hdGl2ZURpZ2VzdD0iMjU2LDI1NywyNTgsMjU5LDI2MiwyNzQsMjc3LDI4NCw1MzAsNTMxLDI4MiwyODMsMjk2LDMwMSwzMTgsMzE5LDUyOSw1MzIsMzA2LDI3MCwyNzEsMjcyLDMwNSwzMTUsMzM0MzI7ODBCQUE2QkNCRkZFNTM5NUZERkUwNjQ4NzY5RTVFNDQiIGV4aWY6UGl4ZWxYRGltZW5zaW9uPSIxMjAiIGV4aWY6UGl4ZWxZRGltZW5zaW9uPSIzNSIgZXhpZjpDb2xvclNwYWNlPSIxIiBleGlmOk5hdGl2ZURpZ2VzdD0iMzY4NjQsNDA5NjAsNDA5NjEsMzcxMjEsMzcxMjIsNDA5NjIsNDA5NjMsMzc1MTAsNDA5NjQsMzY4NjcsMzY4NjgsMzM0MzQsMzM0MzcsMzQ4NTAsMzQ4NTIsMzQ4NTUsMzQ4NTYsMzczNzcsMzczNzgsMzczNzksMzczODAsMzczODEsMzczODIsMzczODMsMzczODQsMzczODUsMzczODYsMzczOTYsNDE0ODMsNDE0ODQsNDE0ODYsNDE0ODcsNDE0ODgsNDE0OTIsNDE0OTMsNDE0OTUsNDE3MjgsNDE3MjksNDE3MzAsNDE5ODUsNDE5ODYsNDE5ODcsNDE5ODgsNDE5ODksNDE5OTAsNDE5OTEsNDE5OTIsNDE5OTMsNDE5OTQsNDE5OTUsNDE5OTYsNDIwMTYsMCwyLDQsNSw2LDcsOCw5LDEwLDExLDEyLDEzLDE0LDE1LDE2LDE3LDE4LDIwLDIyLDIzLDI0LDI1LDI2LDI3LDI4LDMwOzE5ODgzNTA1RDEyMThGMEU5N0IyNzk2RTk2RTZDQTg5Ij4gPHhhcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InV1aWQ6RkU4MjU0OTIxOEQ5REQxMUFBMTU4RjFEOTlCNkE1OTAiIHN0UmVmOmRvY3VtZW50SUQ9InV1aWQ6RkU4MjU0OTIxOEQ5REQxMUFBMTU4RjFEOTlCNkE1OTAiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg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');
	    }
	    
		if (function_exists('imagecreatetruecolor')) {
			$this->im = imagecreatetruecolor($this->width, $this->height);
		}else{
			$this->im = imagecreate($this->width, $this->height);
		}
		$this->getCode();
		$this->buildColors();

		$this->drawBacground();
		$this->drawNoise();
		$this->drawLines();
		$this->drawWord();

		if (!headers_sent()) { //Check if the headers are sent!
			header("Content-Type: image/".$this->mime);
			if ($this->mime == 'jpg') {
				imagejpeg($this->im, null, 90);
			}elseif ($this->mime == 'png') {
				imagepng($this->im);
			}else{
				imagegif($this->im);
			}
			imagedestroy($this->im);
		}else{
			die("Cannot show image: Headers already sent!");
		}
	}
	/**
	 * 
	 * Draw background function
	 * @access private
	 */
	protected function drawBacground(){
		imagefilledrectangle($this->im, 0, 0, $this->width, $this->height, $this->bgcolor);
	}
	/**
	 * 
	 * Draw word function
	 * @access private
	 */
	protected function drawWord(){
		$hmid = ($this->height/2)+6;

		$code_arr = preg_split("//", $this->code_display, -1, PREG_SPLIT_NO_EMPTY);

		foreach($code_arr as $pad => $letter){
			$angle = rand(-20, 20);
			$size = $this->text_size+rand(-3,4);
			
			if($this->mode == 3){
				$size = $this->text_size;
				if($pad == 2){
					$angle = 0;
					if($letter == 'x') $size = $size - 8;
				}
			}
			$pad += 0.25;

			if(function_exists('imagettftext') && $this->ttf){
				imagettftext($this->im, $size, $angle, $pad*$this->text_size, $hmid+2, $this->textcolor, $this->ttf_path.$this->ttf, $letter);
			}else{
				imagestring($this->im, $size, $pad*$this->text_size , $hmid-5 , $letter, $this->textcolor);
			}
		}
	}
    /**
     * Draws distorted lines on the image
     * 
     * @access private
     */
    protected function drawLines(){
        for ($line = 0; $line < $this->num_lines; ++ $line) {
            $x = $this->width * (1 + $line) / ($this->num_lines + 1);
            $x += (0.5 - $this->frand()) * $this->width / $this->num_lines;
            $y = rand($this->height * 0.1, $this->height * 0.9);
            
            $theta = ($this->frand() - 0.5) * M_PI * 0.7;
            $w = $this->width;
            $len = rand($w * 0.4, $w * 0.7);
            $lwid = rand(0, 2);
            
            $k = $this->frand() * 0.6 + 0.2;
            $k = $k * $k * 0.5;
            $phi = $this->frand() * 6.28;
            $step = 0.5;
            $dx = $step * cos($theta);
            $dy = $step * sin($theta);
            $n = $len / $step;
            $amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
            $x0 = $x - 0.5 * $len * cos($theta);
            $y0 = $y - 0.5 * $len * sin($theta);
            
            $ldx = round(- $dy * $lwid);
            $ldy = round($dx * $lwid);
            
            for ($i = 0; $i < $n; ++ $i) {
                $x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
                $y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
                
                imagefilledrectangle($this->im, $x, $y, $x + $lwid, $y + $lwid, $this->linecolor);
            }
        }
    }
    /**
     * 
     * Generate random float
     * @access private
     */
    protected function frand(){
        return 0.0001 * rand(0,9999);
    }    
    /**
     * Draws random noise on the image
     * @access private
     */
    protected function drawNoise(){
        if ($this->noise_level > 10) {
            $noise_level = 10;
        } else {
            $noise_level = $this->noise_level;
        }
        
        $noise_level *= 125;
        
        $points = $this->width*$this->height*$this->iscale;
        $height = $this->height*$this->iscale;
        $width  = $this->width*$this->iscale;
        
        for ($i = 0; $i < $noise_level; ++$i) {
            $x = rand(10, $width);
            $y = rand(10, $height);
            $size = rand(1, 7);
            if ($x - $size <= 0 && $y - $size <= 0) continue;
            
            imagefilledarc($this->im, $x, $y, $size, $size, 0, 360, $this->noisecolor, IMG_ARC_PIE);
        }
    }
    /**
     * 
     * Build colors function
     * @access private
     */
    protected function buildColors(){
        $color = BASIC::init()->convertHTMLtoRGB($this->bg_color);
        $this->bgcolor = imagecolorallocate($this->im, $color['r'], $color['g'], $color['b']);
        
        $alpha = intval($this->transparency / 100 * 127);
        
      	$color = BASIC::init()->convertHTMLtoRGB($this->text_color);
        $this->textcolor = imagecolorallocatealpha($this->im, $color['r'], $color['g'], $color['b'], $alpha);
        
      	$color = BASIC::init()->convertHTMLtoRGB($this->line_color);
        $this->linecolor = imagecolorallocatealpha($this->im, $color['r'], $color['g'], $color['b'], $alpha);
        
      	$color = BASIC::init()->convertHTMLtoRGB($this->noise_color);
        $this->noisecolor = imagecolorallocatealpha($this->im, $color['r'], $color['g'], $color['b'], $alpha);
    }
}
/**
 * 
 * Mail Api interface class
 * 
 * @author Evgeni Baldziyski
 */
interface MailApiInterface{
	/**
	 * Setter/getter for "from"
	 *
	 * @param string $address
	 * @param string [$name]
	 * @return string 
	 */
	function from($address,$name = null);
	/**
	 * Setter/getter
	 *
	 * @param string $key
	 * @param string [$value]
	 * @return string
	 */
	function headers($key,$value = null);
	/**
	 * Setter/getter for charset
	 *
	 * @param string $encoding
	 * @return string
	 */
	function charset($encoding = null);
	/**
	 * Setter/getter for subject
	 *
	 * @param string $post
	 * @return string
	 */
	function subject($post = null);
	/**
	 * Setter/getter for bcc
	 *
	 * @param array [$list]
	 * @return array
	 */
	function bcc($list = null);
	/**
	 * Setter/getter for cc
	 *
	 * @param array [$list]
	 * @return array
	 */
	function cc($list = null);
	/**
	 * Setter/getter for reply
	 *
	 * @param array [$list]
	 * @return array
	 */
	function reply($list = null);
	/**
	 * Setter/getter for attach
	 *
	 * @param string $fname
	 * @param string $name
	 * @param string $cid
	 * @param string $enctype
	 * @return array
	 */
	function attach ($fname,$name = null,$cid = null, $enctype = null);
}
/**
 * 
 * Mail class
 * 
 * ChangeLog:
 * change protected $LE = "\r\n" with PHP_EOL - The system's new line format (\n for Linux, \r\n for Windows, etc)
 * 
 * @author Evgeni Baldzhiyski
 * @version 1.3
 * @since 05.02.2013
 * 
 * 
 */
class BasicMail implements MailApiInterface{
	
	protected $htmlBody = '';
	protected $plainBody = '';
	/**
	 * 
	 * Subject of the email
	 * @var string
	 * @access private
	 */
	protected $subject = '';
	/**
	 * 
	 * Boundary type mail
	 * @var string
	 * @access private
	 */
	protected $boundary = '';
	/**
	 * 
	 * Charset of the mail
	 * @var string
	 */
	protected $charset = 'utf-8';
	/**
	 * 
	 * New line param
	 * @var string
	 * @access private
	 */
	protected $LE = PHP_EOL;
	/**
	 * 
	 * From data
	 * @var array
	 * @access private
	 */
	protected $from = array(
		'name' => '',
		'address' => ''
	);
	/**
	 * 
	 * BCC data
	 * @var array
	 * @access private
	 */
	protected $bcc = array();
	/**
	 * 
	 * CC data
	 * @var array
	 * @access private
	 */
	protected $cc = array();
	/**
	 * 
	 * Reply data
	 * @var array
	 * @access private
	 */
	protected $reply = array();
	/**
	 * 
	 * Attach data
	 * @var array
	 * @access private
	 */
	protected $attach = array();
	/**
	 * 
	 * Header of the mail
	 * @var array
	 * @access private
	 */
	protected $header = array();
	/**
	 * 
	 * Mime of the mail
	 * @var array
	 * @access private
	 */
    protected $mimes = array(
      'hqx'  =>  'application/mac-binhex40',
      'cpt'   =>  'application/mac-compactpro',
      'doc'   =>  'application/msword',
      'bin'   =>  'application/macbinary',
      'dms'   =>  'application/octet-stream',
      'lha'   =>  'application/octet-stream',
      'lzh'   =>  'application/octet-stream',
      'exe'   =>  'application/octet-stream',
      'class' =>  'application/octet-stream',
      'psd'   =>  'application/octet-stream',
      'so'    =>  'application/octet-stream',
      'sea'   =>  'application/octet-stream',
      'dll'   =>  'application/octet-stream',
      'oda'   =>  'application/oda',
      'pdf'   =>  'application/pdf',
      'ai'    =>  'application/postscript',
      'eps'   =>  'application/postscript',
      'ps'    =>  'application/postscript',
      'smi'   =>  'application/smil',
      'smil'  =>  'application/smil',
      'mif'   =>  'application/vnd.mif',
      'xls'   =>  'application/vnd.ms-excel',
      'csv'   =>  'application/vnd.ms-excel',
      'ppt'   =>  'application/vnd.ms-powerpoint',
      'wbxml' =>  'application/vnd.wap.wbxml',
      'wmlc'  =>  'application/vnd.wap.wmlc',
      'dcr'   =>  'application/x-director',
      'dir'   =>  'application/x-director',
      'dxr'   =>  'application/x-director',
      'dvi'   =>  'application/x-dvi',
      'gtar'  =>  'application/x-gtar',
      'php'   =>  'application/x-httpd-php',
      'php4'  =>  'application/x-httpd-php',
      'php3'  =>  'application/x-httpd-php',
      'phtml' =>  'application/x-httpd-php',
      'phps'  =>  'application/x-httpd-php-source',
      'js'    =>  'application/x-javascript',
      'swf'   =>  'application/x-shockwave-flash',
      'sit'   =>  'application/x-stuffit',
      'tar'   =>  'application/x-tar',
      'tgz'   =>  'application/x-tar',
      'xhtml' =>  'application/xhtml+xml',
      'xht'   =>  'application/xhtml+xml',
      'zip'   =>  'application/zip',
      'mid'   =>  'audio/midi',
      'midi'  =>  'audio/midi',
      'mpga'  =>  'audio/mpeg',
      'mp2'   =>  'audio/mpeg',
      'mp3'   =>  'audio/mpeg',
      'aif'   =>  'audio/x-aiff',
      'aiff'  =>  'audio/x-aiff',
      'aifc'  =>  'audio/x-aiff',
      'ram'   =>  'audio/x-pn-realaudio',
      'rm'    =>  'audio/x-pn-realaudio',
      'rpm'   =>  'audio/x-pn-realaudio-plugin',
      'ra'    =>  'audio/x-realaudio',
      'rv'    =>  'video/vnd.rn-realvideo',
      'wav'   =>  'audio/x-wav',
      'bmp'   =>  'image/bmp',
      'gif'   =>  'image/gif',
      'jpeg'  =>  'image/jpeg',
      'jpg'   =>  'image/jpeg',
      'jpe'   =>  'image/jpeg',
      'png'   =>  'image/png',
      'tiff'  =>  'image/tiff',
      'tif'   =>  'image/tiff',
      'css'   =>  'text/css',
      'html'  =>  'text/html',
      'htm'   =>  'text/html',
      'shtml' =>  'text/html',
      'txt'   =>  'text/plain',
      'text'  =>  'text/plain',
      'log'   =>  'text/plain',
      'rtx'   =>  'text/richtext',
      'rtf'   =>  'text/rtf',
      'xml'   =>  'text/xml',
      'xsl'   =>  'text/xml',
      'mpeg'  =>  'video/mpeg',
      'mpg'   =>  'video/mpeg',
      'mpe'   =>  'video/mpeg',
      'qt'    =>  'video/quicktime',
      'mov'   =>  'video/quicktime',
      'avi'   =>  'video/x-msvideo',
      'movie' =>  'video/x-sgi-movie',
      'doc'   =>  'application/msword',
      'word'  =>  'application/msword',
      'xl'    =>  'application/excel',
      'eml'   =>  'message/rfc822'
    );
	/**
	 * __constructor
	 *
	 * @param string $from
	 * @param string [$name]
	 * @param array [$options]
	 * @return BASIC_Mail2
	 */
	function __construct($address, $name = '', $options = array()){
		
		$this->boundary = md5(uniqid(microtime()));

		$this->from = array(
			'name' => $name,
			'address' => $address
		);
		$this->headers('FROM',($name ? $name." <".$address.">" : $address." <".$address.">"));
		$this->headers("MIME-Version","1.0");
		$this->headers("Content-Type",'multipart/related; boundary="'.$this->boundary.'"');
		$this->headers("Content-Transfer-Encoding","7bit");
		
		foreach ($options as $k => $v){
			if($k == 'headers'){
				foreach ($v as $header_key => $header_val){
					$this->headers($header_key,$header_val);
				}
			}else if($k == 'charset'){
				$this->charset($v);
			}else if($k == 'subject'){
				$this->subject($v);
			}else if($k == 'body'){
				$this->body($v);
			}else if($k == 'cc'){
				$this->cc($v);
			}else if($k == 'bcc'){
				$this->bcc($v);
			}
		}
	}
	/**
	 * Set from 
	 * @param string $type
	 * @param string $value
	 * @see MailApiInterface::from()
	 */
	function from($type,$value = null){
		if($value === null) return $this->from[$type];
		$this->from[$type] = $value;
		return $this;
	}
	/**
	 * Set mail headers
	 * @param string $key
	 * @param mixed [$value]
	 * @see MailApiInterface::headers()
	 */
	function headers($key,$value = null){
		if($value === null) return isset($this->header[$key]) ? $this->header[$key] : ''; 
		if($key === null) $this->header;
		
		$this->header[$key] = $value;
		return $this;
	}
	/**
	 * Set charset of the mail
	 * @param string [$encoding]
	 * @see MailApiInterface::charset()
	 */
	function charset($encoding = null){
		if($encoding === null) return $this->charset;
		$this->charset = $encoding;
		return $this;
	}
	/**
	 * 
	 * Set the body of the mail
	 * 
	 * @param mixed [$post]
	 * @param boolean [$append]
	 */
	function body($post = null, $append = true){
		if($post == null) return $this->htmlBody;
		if(!$append){
			$this->htmlBody = $post;
		}else{
			$this->htmlBody .= $post;
		}
		return $this;
	}
	/**
	 * Set/concat plain text body
	 * 
	 * @param unknown_type $post
	 * @param unknown_type $append
	 */
	function text($post = null, $append = true){
		if($post == null) return $this->plainBody;
		if(!$append){
			$this->plainBody = $post;
		}else{
			$this->plainBody .= $post;
		}
		return $this;
	}
	/**
	 * Set the subject of the mail
	 * @see MailApiInterface::subject()
	 * @param mixed [$post]
	 */
	function subject($post = null){
		if($post === null) return $this->subject;
		$this->subject = $post;
		return $this;
	}
	/**
	 * Set bccs
	 * @see MailApiInterface::bcc()
	 * @param mixed [$list]
	 */
	function bcc($list = null){
		if($list === null) return $this->bcc;
		if(is_numeric($list)) return isset($this->bcc[$list]) ? $this->bcc[$list] : '';
		if(!is_array($list)) $list = array($list);
		foreach ($list as $v){
			$this->bcc[] = $v;
		}
		return $this;
	}
	/**
	 * Set ccs
	 * @see MailApiInterface::cc()
	 * @param mixed $list
	 */
	function cc($list = null){
		if($list === null) return $this->cc;
		if(is_numeric($list)) return isset($this->cc[$list]) ? $this->cc[$list] : '';
		if(!is_array($list)) $list = array($list);
		foreach ($list as $v){
			$this->cc[] = $v;
		}
		return $this;
	}
	/**
	 * Set reply
	 * @see MailApiInterface::reply()
	 * @param mixed $list
	 */
	function reply($list = null){
		if($list === null) return $this->reply;
		if(is_numeric($list)) return isset($this->reply[$list]) ? $this->reply[$list] : '';
		if(!is_array($list)) $list = array($list);
		foreach ($list as $v){
			$this->reply[] = $v;
		}
		return $this;
	}
	/**
	 * File attach. The "name" attribte will be concat with fname's ext ex:fname == photi.jpg -> name+".jpg"
	 * For Thnderbird $cid is very important! If use $cid you must link this attach in email html.Ex: 
	 * 		PHP 
	 * 			mail->attach('img/my_email_attach_photo.jpg', 'photo', 'photi_cid');
	 * 
	 * 		HTML
	 * 			<p>This is my email html</p>
	 * 			<img src="cid:photi_cid">
	 *
	 * @param string $fname [my_file_name.ext]
	 * @param string [$name]
	 * @param string [$cid]
	 * @param string [$enctype]
	 * @return BASIC_Mail2
	 */
	function attach ($fname, $name = null, $cid = null, $enctype = null){
		if($name === null && $cid === null && $enctype === null){
			return isset($this->attach[$fname]) ? $this->attach[$fname] : '';
		}
		$this->attach[$fname] = array(
			'cid' => $cid,
			'name' => $name,
			'enctype' => $enctype,
			'source' => ''
		);
		return $this;
	}
	/**
	 * 
	 * Attach the exist file to mail
	 * 
	 * @param string $fname [my_file_name.ext]
	 * @param bin/string $source
	 * @param string [$enctype]
	 * @param string [$name]
	 * @param string [$cid]
	 * @return BASIC_Mail2
	 */
	function attach_source($fname, $source, $enctype = null, $name = null, $cid = null){
		if($name === null && $cid === null && $enctype === null){
			return isset($this->attach[$fname]) ? $this->attach[$fname] : '';
		}
		$this->attach[$fname] = array(
			'cid' => $cid,
			'name' => $name,
			'enctype' => $enctype,
			'source' => $source
		);
		return $this;
	}
	/**
	 * Send the prepared email
	 *
	 * @param array/string $to
	 */
	function send($to){
		if(is_array($to)){
			if(count($to) > 1){
				$this->bcc($to);
				$to = '';
			}else{
				$to = $to[0];
			}
		}
		
		if($this->bcc()){
			$this->header['Bcc'] = implode(",",$this->bcc());
		}
		if($this->cc()){
			$this->header['Cc'] = implode(",",$this->cc());
		}
		if($this->reply()){
			$this->header['Reply-To'] = implode(",",$this->reply());
		}
		
		$attach_body = $this->_attach();
		
		$header = ''; foreach ($this->header as $key=>$value) {
			$header .= $key.": ".$value."".$this->LE;
		}
		
		$SBody = '';
		
		if($body = $this->text()){
			$SBody .= $this->LE;
			$SBody .= $this->LE;
			$SBody .= "--{$this->boundary}".$this->LE;
			$SBody .= "Content-Type: text/plain; charset=".$this->charset." format=flowed".$this->LE;
			$SBody .= "Content-Transfer-Encoding: 7bit".$this->LE;
			$SBody .= $this->LE;
		
			$SBody .= $body;
		}
		if($body = $this->body()){
			$SBody .= $this->LE;
			$SBody .= $this->LE;
			$SBody .= "--{$this->boundary}".$this->LE;
			$SBody .= "Content-Type: text/html; charset=".$this->charset."".$this->LE;
			$SBody .= "Content-Transfer-Encoding: 7bit".$this->LE;
			$SBody .= $this->LE;
		
			$SBody .= $body;
		}
				
		$SBody .= $attach_body;
		
		$SBody .=  $this->LE;
		$SBody .=  "--{$this->boundary}--";

		if(!mail($to, $this->EncodeHeader($this->subject()), $SBody, $header)){  
			return false;
		}
		return true;
	}
	/**
	 * 
	 * Attach function helper
	 * @access private
	 * @throws Exception
	 */
	 protected function _attach (){
		$return  = '';
		
		if($this->attach){
			$have_cid = false;
			$this->headers("Content-Type", 'multipart/mixed; boundary="'.$this->boundary.'"');
			
			foreach($this->attach as $fname => $context){
				$enctype = $context['enctype'];
				$cid = $context['cid'];
				$name = $context['name'];
				
				if(!$enctype){
					$split = explode(".",$fname);
					$extension = $split[count($split) - 1];
					$enctype = isset($this->mimes[$extension]) ? $this->mimes[$extension] : 'N/A';
				}
				if($context['source']){
					$file = $context['source'];	
				}else{
					$file = @file($fname);
				}
				if($file){
					if(!$name){
						$ex = explode("/",$fname);
						$name = $ex[count($ex)-1];
					}
					$file = base64_encode(implode("", $file));
					
					$return .= $this->LE.$this->LE;
					$return .= "--".$this->boundary.$this->LE;
					$return .= 'Content-Type: '.$enctype.'; name="'.$name.'"'.$this->LE; //; name=\"$description\";
					$return .= 'Content-Transfer-Encoding: base64'.$this->LE;
					
					$disposition = 'attachment';
					if($cid){
						$have_cid = true;
						$disposition = 'inline';
						$return .= 'Content-ID: <'.$cid.'>'.$this->LE;
					}
					$return .= 'Content-Disposition: '.$disposition.'; filename="'.$name.'"'.$this->LE.$this->LE;
		
					$return .= chunk_split($file, 72, $this->LE);
				}else{
					throw new Exception('Can not find file "'.$file.'" !');
				}
			}
			if($have_cid){
				$this->headers("Content-Type", 'multipart/related; boundary="'.$this->boundary.'"');
			}
		}
		return $return;
	}
	/**
	 * Encode a header string to best of Q, B, quoted or none.
	 * @access private
	 * @param string $str
	 * @return string
	 */
	 function EncodeHeader ($str) {
		$x = 0;
		
		$x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
		
		if ($x == 0) return ($str);
		
		$maxlen = 75 - 7 - strlen($this->charset);
		/* Try to select the encoding which should produce the shortest output */
		if (strlen($str)/3 < $x) {
			$encoding = 'B';
			$encoded = base64_encode($str);
			$maxlen -= $maxlen % 4;
			$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
		} else {
			$encoding = 'Q';
			$encoded = $this->EncodeQ($str);
			$encoded = $this->WrapText($encoded, $maxlen, true);
			$encoded = str_replace('='.$this->LE, "\n", trim($encoded));
		}
		$encoded = preg_replace('/^(.*)$/m', " =?".$this->charset."?$encoding?\\1?=", $encoded);
		$encoded = trim(str_replace("\n", $this->LE, $encoded));
		return $encoded;
	}

  /**
   * Encode string to quoted-printable.
   * @access private
   * @param string [$input]
   * @param int [$line_max]
   * @param boolean [$space_conv]
   * @return string
   */
	protected function EncodeQP( $input = '', $line_max = 76, $space_conv = false ) {
		$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$lines = preg_split('/(?:\r\n|\r|\n)/', $input);
		$eol = "\r\n";
		$escape = '=';
		$output = '';
		while( list(, $line) = each($lines) ) {
			$linlen = strlen($line);
			$newline = '';
			for($i = 0;$i < $linlen; $i++) {
				$c = substr( $line, $i, 1 );
				$dec = ord( $c );
				if ( ( $i == 0 ) && ( $dec == 46 ) ) { // convert first point in the line into =2E
				  	$c = '=2E';
				}
				if ( $dec == 32 ) {
					if ( $i == ( $linlen - 1 ) ) { // convert space at eol only
					   $c = '=20';
					} else if ( $space_conv ) {
					   $c = '=20';
					}
				} elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
					$h2 = floor($dec/16);
					$h1 = floor($dec%16);
					$c = $escape.$hex[$h2].$hex[$h1];
				}
				if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
					$output .= $newline.$escape.$eol; //  soft line break; " =\r\n" is okay
					$newline = '';
					// check if newline first character will be point or not
					if ( $dec == 46 ) {
						$c = '=2E';
					}
				}
				$newline .= $c;
			} // end of for
			$output .= $newline.$eol;
		} // end of while
		return trim($output);
	}
   /**
    * 
    * Encode string to q encoding.
    * 
    * @access private
    * @param string $str
    * @return string
    */
	protected function EncodeQ ($str) {
		$encoded = preg_replace("[\r\n]", '', $str);
		$encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',"'='.sprintf('%02X', ord('\\1'))", $encoded);
		$encoded = str_replace(' ', '_', $encoded);
		return $encoded;
	}
   /**
    * Wraps message for use with mailers that do not
    * automatically perform wrapping and for quoted-printable.
    * Original written by philippe.
    * @access private
    * @param string $message
    * @param int $length
    * @param boolean $qp_mode
    * @return string
    */
	protected function WrapText($message, $length, $qp_mode = false) {
		$soft_break = ($qp_mode) ? sprintf(" =%s", $this->LE) : $this->LE;
		$message = $this->FixEOL($message);
		if (substr($message, -1) == $this->LE) {
			$message = substr($message, 0, -1);
		}
		$line = explode($this->LE, $message);
		$message = '';
		for ($i=0 ;$i < count($line); $i++) {
			$line_part = explode(' ', $line[$i]);
			$buf = '';
			for ($e = 0; $e<count($line_part); $e++) {
				$word = $line_part[$e];
				if ($qp_mode and (strlen($word) > $length)) {
					$space_left = $length - strlen($buf) - 1;
					if ($e != 0) {
						if ($space_left > 20) {
							$len = $space_left;
							if (substr($word, $len - 1, 1) == '=') {
								$len--;
							} elseif (substr($word, $len - 2, 1) == '=') {
								$len -= 2;
							}
							$part = substr($word, 0, $len);
							$word = substr($word, $len);
							$buf .= ' ' . $part;
							$message .= $buf . sprintf("=%s", $this->LE);
						} else {
							$message .= $buf . $soft_break;
						}
						$buf = '';
					}
					while (strlen($word) > 0) {
						$len = $length;
						if (substr($word, $len - 1, 1) == '=') {
							$len--;
						} elseif (substr($word, $len - 2, 1) == '=') {
							$len -= 2;
						}
						$part = substr($word, 0, $len);
						$word = substr($word, $len);
						
						if (strlen($word) > 0) {
							$message .= $part . sprintf("=%s", $this->LE);
						} else {
							$buf = $part;
						}
					}
				} else {
					$buf_o = $buf;
					$buf .= ($e == 0) ? $word : (' ' . $word);
					if (strlen($buf) > $length and $buf_o != '') {
						$message .= $buf_o . $soft_break;
						$buf = $word;
					}
				}
			}
			$message .= $buf . $this->LE;
		}
		return $message;
	}
    /**
     * Changes every end of line from CR or LF to CRLF.
     * @access private
     * @param string $str
     * @return string
     */
    protected function FixEOL($str) {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $str = str_replace("\n", $this->LE, $str);
        return $str;
    }
}