<?php
/*
Plugin Name: AppFreeWeb Widget
Plugin URI: http://www.appfreeweb.com/widget/
Description: Adds a sidebar widget to display AppFreeWeb discounted apps. Add it to your sidebar in the <A HREF='widgets.php'>Widget Configuration Page</A>
Version: 1.0.0
Author: AppFreeWeb
Author URI: http://www.appfreeweb.com/
License: GPL

This software comes without any warranty, express or otherwise.

*/

function widget_appfreeweb_init()
{

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_appfreeweb($args)
        {

		// "$args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys." - These are set up by the theme
		extract($args);

		// These are our own options
		$options = get_option('widget_appfreeweb');
		$title = $options['title'];  // Title in sidebar for widget
        $width = $options['width'];  // Width
        $show = $options['show'];  // # of Updates to show
                $category=$options['category'];
                $currency=$options['currency'];

                $tiers_file=file_get_contents(dirname(__FILE__).'/tiers.csv');
                $tiers=split("\n",$tiers_file);
                $i=0;
                foreach($tiers as $tier)
                {
                    $tiers[$i]=split(",",$tier);
                    $i++;
                }
                
                //Download and Caching of the data
                $device="iphone";
                $section="free";
                
                switch($currency)
                {
                    case 1:
                        $currency_symbol_left="$";
                        $currency_symbol_right="";
                        break;
                    case 2:
                        $currency_symbol_left="$";
                        $currency_symbol_right="";
                        break;
                    case 3:
                        $currency_symbol_left="";
                        $currency_symbol_right="€";
                        break;
                    case 4:
                        $currency_symbol_left="£";
                        $currency_symbol_right="";
                        break;
                    case 5:
                        $currency_symbol_left="";
                        $currency_symbol_right="¥";
                        break;
                    case 6:
                        $currency_symbol_left="$";
                        $currency_symbol_right="";
                        break;
                }
                $url='http://www.appfreeweb.com/widget_data/'.$device."/".$section.'_'.$category.'.html?siteURL='.site_url();
                $data = DownloadAndCache::descargar($url, 60*60*4);
                $data_array=unserialize(base64_decode($data));

                if($data_array=="")
                    return;

                // Output
		echo $before_widget ;
                if(count($data_array)<$show)
                    $show=count($data_array);
                
		// start
                $height=67*$show+25;
		echo '<div id="AppFreeWeb_div" style="width: '.$width.'px;">';
                echo $before_title.$title.$after_title;
		echo '<ul id="AppFreeWeb_update_list"  style="list-style: none; ">';
                foreach($data_array as $data)
                {
                    echo "<li>";
                    echo '<div style="vertical-align:text-top; height: 57px;">';
                    echo '<A HREF="'.$data[2].'" style="border-top-left-radius: 10px;
                                      border-bottom-right-radius: 10px;
                                      border-top-right-radius: 10px;
                                      border-bottom-left-radius: 10px;
                                      float: left; padding: 0px 0px 0px 0px;
                                      overflow: hidden;
                                      text-overflow: ellipsis;
                                      -o-text-overflow: ellipsis;
                                      white-space: nowrap;
                                      width: 100%;">';

                    echo '<img style="border-top-left-radius: 10px;
                                      border-bottom-right-radius: 10px;
                                      border-top-right-radius: 10px;
                                      border-bottom-left-radius: 10px;
                                      float: left; padding: 0px 0px 0px 0px;
                                      " src="'.$data[1].'">';
                    //if(strlen($data[0])>17)$data[0]=substr($data[0],0,14)."...";
                    echo "&nbsp;".$data[0];
                    echo "<BR>";
                    echo "&nbsp;<STRIKE><font color=RED><B>$currency_symbol_left".$tiers[$data[3]][$currency]."$currency_symbol_right</B></font></STRIKE>";
                    echo "<BR>";
                    echo "&nbsp;<font color=GREEN><B>". __('FREE!') ."</B></font>";
                    echo "</A>";
                    echo "</div>";
                    echo "</li>";
                    $show--;
                    if($show==0)break;
                }
                echo '</ul>';
                echo "<div align=center><A HREF='http://www.appfreeweb.com/' alt='Best iPhone Apps' title='Best iPhone Apps'>Best iPhone Apps</A> <sup><A HREF='http://www.appfreeweb.com/widget/' alt='Add this widget to your blog' title='Add this widget to your blog'>?</A></sup></div>";
                echo '</div>';                

		// echo widget closing tag
		echo $after_widget;
	}

	// Settings form
	function widget_appfreeweb_control()
        {

		// Get options
		$options = get_option('widget_appfreeweb');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('title'=>'Today\'s Bargains', 'width'=>'200', 'show'=>'5','category'=>'1','currency'=>'1');

                // form posted?
		if ( $_POST['AppFreeWeb-submit'] )
                {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['AppFreeWeb-title']));
            $options['width'] = strip_tags(stripslashes($_POST['AppFreeWeb-width']));
			$options['show'] = strip_tags(stripslashes($_POST['AppFreeWeb-show']));
                        if($options['show']>5)$options['show'] = 5;
			$options['category'] = strip_tags(stripslashes($_POST['AppFreeWeb-category']));
			$options['currency'] = strip_tags(stripslashes($_POST['AppFreeWeb-currency']));
			update_option('widget_appfreeweb', $options);
		}

		// Get options for form fields to show
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
        $show = htmlspecialchars($options['show'], ENT_QUOTES);
        $width = htmlspecialchars($options['width'], ENT_QUOTES);
		$category = htmlspecialchars($options['category'], ENT_QUOTES);
		$currency = htmlspecialchars($options['currency'], ENT_QUOTES);

                $categories=array(1=>"All",
                                    9=>"Games",
                                    10=>"Books",
                                    14=>"Economy",
                                    4=>"Education",
                                    5=>"Entertainment",
                                    7=>"Finance",
                                    19=>"Health",
                                    6=>"Life Style",
                                    11=>"Medicine",
                                    12=>"Music",
                                    13=>"Navigation",
                                    15=>"News",
                                    8=>"Photography",
                                    16=>"Productivity",
                                    18=>"Reference",
                                    17=>"Social",
                                    3=>"Sports",
                                    21=>"Trips",
                                    20=>"Utilities",
                                    2=>"Weather");

                $currencies=array(1=>"$ - USD",
                                    2=>"$ - CAD",
                                    3=>"€ - EUR",
                                    4=>"£ - GBP",
                                    5=>"¥ - YEN",
                                    6=>"$ - AUD");

                
		// The form fields
		echo '<p style="text-align:left;">
				<label for="AppFreeWeb-title">' . __('Title:') . '
				<input style="width: 200px;" id="AppFreeWeb-title" name="AppFreeWeb-title" type="text" value="'.$title.'" />
				</label></p>';
        echo '<p style="text-align:left;">
                <label for="AppFreeWeb-width">' . __('Width (In pixels):') . '
                <input style="width: 200px;" id="AppFreeWeb-width" name="AppFreeWeb-width" type="text" value="'.$width.'" />
                </label></p>';
		echo '<p style="text-align:left;">
				<label for="AppFreeWeb-show">' . __('Show (Max. 5):') . '
				<input style="width: 200px;" id="AppFreeWeb-show" name="AppFreeWeb-show" type="text" value="'.$show.'" />
				</label></p>';
		echo '<p style="text-align:left;">
				<label for="AppFreeWeb-category">' . __('Category:') . '
				<select style="width:200px;" id="AppFreeWeb-category" name="AppFreeWeb-category">';
                                foreach($categories as $idCategory=>$name)
                                {
                                  echo '<option value="'.$idCategory.'"';
                                  if($idCategory==$category) 
                                      echo ' selected';
                                  echo '>'.$name.'</option>';
                                }
                echo           '</select>
				</label></p>';
		echo '<p style="text-align:left;">
				<label for="AppFreeWeb-currency">' . __('Currency:') . '
				<select style="width:200px;" id="AppFreeWeb-currency" name="AppFreeWeb-currency">';
                                foreach($currencies as $idCurrency=>$name)
                                {
                                  echo "<option value=\"$idCurrency\"";
                                  if($idCurrency==$currency)
                                      echo " selected";
                                  echo ">$name</option>";
                                }
                echo           '</select>
                                </label></p>';
                echo '<input type="hidden" id="AppFreeWeb-submit" name="AppFreeWeb-submit" value="1" />';
	}


	// Register widget for use
	//register_sidebar_widget(array('AppFreeWeb', 'widgets'), 'widget_appfreeweb');
    wp_register_sidebar_widget( 'AppFreeWeb', 'AppFreeWeb', 'widget_appfreeweb', array('description' => __('Adds a sidebar widget to display AppFreeWeb updates')) );

	// Register settings for use, 300x200 pixel form
	//register_widget_control(array('AppFreeWeb', 'widgets'), 'widget_appfreeweb_control', 300, 200);
    wp_register_widget_control( 'AppFreeWeb', 'AppFreeWeb', 'widget_appfreeweb_control');
}

// Run code and init
require_once(dirname(__FILE__).'/downloadAndCache.php');

$uploadDir=wp_upload_dir();
$tmp_dir=$uploadDir['path'];

DownloadAndCache::setPrefix('appfreeweb-cache');
DownloadAndCache::setTmp($tmp_dir."/");

add_action('widgets_init', 'widget_appfreeweb_init');

?>
