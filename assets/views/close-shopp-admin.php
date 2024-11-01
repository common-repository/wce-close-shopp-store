<?php
global $wp;

$linkErr = 'style="display:none;"';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if($_POST["wceredirtype"]=='xlink') {
	    if (empty($_POST["xlinkurl"])) {
	        $linkErr = 'style="display:block;"';
	    }
	} else {
		$linkErr = 'style="display:none;"';
	}
}

$plugindir = dirname( __FILE__ );
$shoppPages = shopp_setting( 'storefront_pages' );

$today = time();
$wceOptions = get_option( 'wce_close_shopp_store_options');

$closedPageTitle = $wceOptions['pagetitle'];
$closedPageContent = $wceOptions['pagecontent'];

$redirType = $wceOptions['redirtype'];
$redirXlink =  $wceOptions['redirxlink'];


$thisYear = date('Y',  $today );
$thisMonth = date('m', $today);
$thisDay = date('d', $today);

$startDay = (!$wceOptions['startDay']=='') ? $wceOptions['startDay'] : $thisDay;
$startMonth = (!$wceOptions['startMonth']=='') ? $wceOptions['startMonth'] : $thisMonth;
$startYear = (!$wceOptions['startYear']=='') ? $wceOptions['startYear'] : $thisYear;

$endDay = (!$wceOptions['endDay']=='') ? $wceOptions['endDay'] : $thisDay;
$endMonth = (!$wceOptions['endMonth']=='') ? $wceOptions['endMonth'] : $thisMonth;
$endYear = (!$wceOptions['endYear']=='') ? $wceOptions['endYear'] : $thisYear;

/**
    *
    * @Create dropdown of years
    *
    * @param int $start_year
    *
    * @param int $end_year
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
    function createYears($start_year, $end_year, $id='year_select', $selected=null)
    {

        /*** the current year ***/
		$nowyear = date('Y');
        $selected = is_null($selected) ? $nowyear : $selected;

        /*** range of years ***/
        $r = range($start_year, $end_year);

        /*** create the select ***/
        $select = '<select name="'.$id.'" id="'.$id.'">';
        foreach( $r as $year )
        {
            $select .= "<option value=\"$year\"";
            $select .= ($year==$selected) ? ' selected="selected"' : '';
            $select .= ">$year</option>\n";
        }
        $select .= '</select>';
        return $select;
    }

    /*
    *
    * @Create dropdown list of months
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
     function createMonths($id='month_select', $selected=null)
    {
        /*** array of months ***/
        $months = array(
                1=>'January',
                2=>'February',
                3=>'March',
                4=>'April',
                5=>'May',
                6=>'June',
                7=>'July',
                8=>'August',
                9=>'September',
                10=>'October',
                11=>'November',
                12=>'December');

        /*** current month ***/
        $selected = is_null($selected) ? date('m') : $selected;

        $select = '<select name="'.$id.'" id="'.$id.'">'."\n";
        foreach($months as $key=>$mon)
        {
            $select .= "<option value=\"$key\"";
            $select .= ($key==$selected) ? ' selected="selected"' : '';
            $select .= ">$mon</option>\n";
        }
        $select .= '</select>';
        return $select;
    }


    /**
    *
    * @Create dropdown list of days
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
     function createDays($id='day_select', $selected=null)
    {
        /*** range of days ***/
        $r = range(1, 31);

        /*** current day ***/
        $selected = is_null($selected) ? date('d') : $selected;

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach ($r as $day)
        {
            $select .= "<option value=\"$day\"";
            $select .= ($day==$selected) ? ' selected="selected"' : '';
            $select .= ">$day</option>\n";
        }
        $select .= '</select>';
        return $select;
    }


    /**
    *
    * @create dropdown list of hours
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
     function createHours($id='hours_select', $selected=null)
    {
        /*** range of hours ***/
        $r = range(1, 12);

        /*** current hour ***/
        $selected = is_null($selected) ? date('h') : $selected;

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach ($r as $hour)
        {
            $select .= "<option value=\"$hour\"";
            $select .= ($hour==$selected) ? ' selected="selected"' : '';
            $select .= ">$hour</option>\n";
        }
        $select .= '</select>';
        return $select;
    }

    /**
    *
    * @create dropdown list of minutes
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
     function createMinutes($id='minute_select', $selected=null)
    {
        /*** array of mins ***/
        $minutes = array(0, 15, 30, 45);

    $selected = in_array($selected, $minutes) ? $selected : 0;

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach($minutes as $min)
        {
            $select .= "<option value=\"$min\"";
            $select .= ($min==$selected) ? ' selected="selected"' : '';
            $select .= ">".str_pad($min, 2, '0')."</option>\n";
        }
        $select .= '</select>';
        return $select;
    }

    /**
    *
    * @create a dropdown list of AM or PM
    *
    * @param string $id The name and id of the select object
    *
    * @param string $selected
    *
    * @return string
    *
    */
     function createAmPm($id='select_ampm', $selected=null)
    {
        $r = array('AM', 'PM');

    /*** set the select minute ***/
        $selected = is_null($selected) ? date('A') : strtoupper($selected);

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach($r as $ampm)
        {
            $select .= "<option value=\"$t\"";
            $select .= ($ampm==$selected) ? ' selected="selected"' : '';
            $select .= ">$ampm</option>\n";
        }
        $select .= '</select>';
        return $select;
    }
	
	
	  
		
		function wce_close_shopp_status() {
			$opt = get_option( 'wce_close_shopp_store_options');
			echo $opt['status'];
		}	
?>
<?php $ShoppClosed = ( $_POST['wcecloseshopp'] ) ? $_POST['wcecloseshopp'] : $this->ShoppClosed; ?>
<?php $redirType = ( $_POST['wceredirtype'] ) ? $_POST['wceredirtype'] : $this->redirType; ?>
<div class="wrap">
    <div id="icon-edit" class="icon32"></div>
    <h2>Close Shopp Store Settings</h2>
    
        <h3>Enable / Disable Close Shopp Store</h3>
        <div id="wce-form-wrapper">
        <form action="" method="post">
           <table class="wce-form-table">
                <tr>
                    <td>
                        <ul>
                        <li>
                           <label>
                                   <input type="radio" name="wcecloseshopp" value="open" id="wcecloseshopp_open" <?php echo ( $ShoppClosed == 'open'  )? ' checked="checked"' : ''; ?> />&nbsp;OPEN Shopp Store</label>
                            </li>
                        	<li>
                           <label>
                                   <input type="radio" name="wcecloseshopp" value="closeNow" id="wcecloseshopp_now" <?php echo ( $ShoppClosed == 'closeNow'  )? ' checked="checked"' : ''; ?> />&nbsp;Close Shopp Store Now</label>
                            </li>
                            <li style='display:none;'>
                             <label>
                                  <input type="radio" name="wcecloseshopp" value="closeDates" id="wcecloseshopp_dates" <?php echo ( $ShoppClosed == 'closeDates'  )? ' checked="checked"' : ''; ?> />&nbsp;Close Shopp Store for Dates</label>
							&nbsp;from:  <?php echo createDays('start_day', $startDay); ?> <?php echo createMonths('start_month', $startMonth); ?> <?php echo createYears($thisYear, $thisYear+2, 'start_year', $startYear); ?>&nbsp;to&nbsp;	 <?php echo createDays('end_day', $endDay); ?> <?php echo createMonths('end_month', $endMonth); ?> <?php echo createYears($thisYear, $thisYear+2, 'end_year', $endYear); ?>
                            </li>
                           <li>
                          <p><hr /></p>
 
                          <h3>Choose Re-direction:</h3> 
                            </li>
                           <li>
                          To open a new window on redirect, use the &quot;Closed Page' option below, and place a link to the page or site in the conent area. <br />
Be sure to set the link to open a new window when clicked.<br /><br />
                          </li>
                           <li>
                             <input type="radio" name="wceredirtype" value="xlink" id="wceredirtype_link" <?php echo ( $redirType == 'xlink'  )? ' checked="checked"' : ''; ?> />&nbsp;Redirect to External Link:</label>
                           </li>
                          <li>
                           <input name="xlinkurl" type="url" id="xlinkurl" value="<?php echo $redirXlink; ?>" size="120" />
                           <div class="error" <?php echo $linkErr ?> >&nbsp;&nbsp;<strong>To redirect to an external website, you must enter a URL, or change to using the 'Closed Page' option.</strong></div>
                           </li>
                          <li>
                           <br /><strong>~ OR ~</strong><br /><br />
                           </li>
                            <li>
                            <input type="radio" name="wceredirtype" value="storeclosedpage" id="wceredirtype_page" <?php echo ( $redirType == 'storeclosedpage'  )? ' checked="checked"' : ''; ?> />&nbsp;Redirect to Closed Page</label>
                          </li>
                            <li>
                            <h3>Page Title:</h3> <input name="wceclosedpagetitle" type="text" id="wceclosedpagetitle" value="<?php echo $closedPageTitle; ?>" size="120" />
                            </li>
                            <li>
                            <h3>Page Content: </h3>
                            <?php wp_editor( $closedPageContent, 'wceclosedpagecontent') ?>
                          </li> 
                          <li class="submit">
                                <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"/>
                          </li>                           
                      </ul>
                    </td>
                </tr>
            </table>
        </form> 
        <div>
        <p>
<?php


		$a = $shoppPages;
		 echo 'Redirected Shopp Virtual Page Slugs: <br />';
			foreach ($a as $key => $value) {  
			$sa = $value;
			echo '&nbsp;&nbsp;&nbsp; ' . $key  . ' = ' . $sa['slug'] . '<br />'; 
		} 

?>
        </p>
            
           <div id="message" class="updated">
           Your Store is <?php wce_close_shopp_status(); ?>
           	</div>     
    </div>
</div>