<?php
/* Step 3 of the Sudoku Puzzle solving script set
Here is where the puzzle is viewed, values can be added, and
the user can select to have the puzzle solved. */


# Version: 2.2	07-31-07
# Make process of elimination a distinct selection 7-31-07

require('config.php');
#echo "Connecting to SQL_HOST...<br>";
mysql_connect(SQL_HOST, SQL_USER, SQL_PASS) or die(mysql_error());
#echo "Done<br>";
#echo "Selecting database...<br>";
mysql_select_db("Sudoku_cell") or die(mysql_error());
#echo "Done<br>";

// Determine values of some variables passed in
$column=$_POST['column']; 
$row=$_POST['row']; 
$value=$_POST['value']; 
$enter=$_POST['enter']; 
$quantity=$_POST['quantity']; 
$debug=$_POST['debug']; 
define(DEBUG, $debug);
$stop=0;
$stop=$_POST['stop']; 
define(STOP, $stop);
$stage1=1;
$stage2=1;
$stage3=1;
$stage4=1;
$stage5=1;
if ($_POST['stage1']) {
  $stage1=$_POST['stage1']; 
  $stage2=0;
  $stage3=0;
  $stage4=0;
  $stage5=0;
}
if ($_POST['stage2']) {
  $stage2=$_POST['stage2']; 
  $stage3=0;
  $stage4=0;
  $stage5=0;
}
if ($_POST['stage3']) {
  $stage3=$_POST['stage3']; 
  $stage4=0;
  $stage5=0;
}
if ($_POST['stage4']) {
  $stage4=$_POST['stage4']; 
  $stage5=0;
}
if ($_POST['stage5']) {
  $stage5=$_POST['stage5']; 
}

define(STAGE1, $stage1);
define(STAGE2, $stage2);
define(STAGE3, $stage3);
define(STAGE4, $stage4);
define(STAGE5, $stage5);

// If we have data to be entered, enter it here
if ($enter == "Entered") {
  unset($column);
  unset($row);
  unset($value);
} else if (($column) && ($row)) {
  	echo "TP 0 - data to be entered. quantity: (".$quantity." )<br>";
	for ($i=1; $i<=$quantity; $i++) {
	    echo "TP 1 Update database with column ".$column[$i]." row ".$row[$i]." value: ".$value[$i]."<br>";
	    update_database($column[$i], $row[$i], $value[$i]);
	}
} 


/* MAIN */

$request=$_POST['request'];
if (STOP) {
    /* If step mode has been selected, we know you want to solve the puzzle */
    $request="solve";
    mini_menu();
} elseif ($request) {
	  echo "Request is ".$request."<br>";
	} else {
	  $request="NA";
	}

switch($request) {
	case NA:
		print_puzzle();
		break;
	case solve:
		solve_puzzle(0);
		break;
	case guess:
		solve_puzzle(1);
		break;
	case enter:
		enter_data($quantity);
		break;
	case reset:
		reset_data();
		print_puzzle();
		break;
	case new_puzzle:
		draw_input_puzzle();
		break;
	case fill_database:
		fill_puzzle_database();
		sector_prep();
		#prompt();
		break;
	case check_puzzle:
		$return=check_puzzle(1);
		if ($return) {
		    echo "Puzzle is incorrect!<br>";
		    print_puzzle();
		} else {
		    echo "Puzzle has been solved!<br>";
		    print_puzzle();
		}
		break;
	default:
		print_puzzle();
		break;
}
menu();

function mini_menu() {
	echo "<form method='post' action=$PHP_SELF>";
	echo " Step Mode: <input type='radio' value='1' name='stop'>";
	echo "<input type='submit' value='Enter'><br>";
}

function menu() {
	#echo "<form method='post' action='step3_cell.php'>";
	echo "<form method='post' action=$PHP_SELF>";
	echo "---Select Action:<br>";
	echo "Input new puzzle: <input type='radio' value='new_puzzle' name='request'><br>";
	echo "Solve current puzzle: <input type='radio' value='solve' name='request'>";
	echo " Step Mode: <input type='radio' value='1' name='stop'>";
	echo " Guess Mode: <input type='radio' value='guess' name='request'>";
#	echo "Stage 1<input type='checkbox' name='stage1' value='1'><br>";
#	echo "Stage 2<input type='checkbox' name='stage2' value='1'><br>";
#	echo "Stage 3<input type='checkbox' name='stage3' value='1'><br>";
#	echo "Stage 4<input type='checkbox' name='stage4' value='1'><br>";
#	echo "Stage 5<input type='checkbox' name='stage5' value='1'><br>";

	echo " Debug Enabled: <input type='radio' value='1' name='debug'><br>";
	echo "Reset existing puzzle: <input type='radio' value='reset' name='request'><br>";
	echo "Enter data to existing puzzle: <input type='radio' value='enter' name='request'>";
	echo "  How many data points to enter: <input type='radio' name='quantity' value='1'>1";
	echo "<input type='radio' name='quantity' value='2'>2";
	echo "<input type='radio' name='quantity' value='3'>3";
	echo "<input type='radio' name='quantity' value='4'>4";
	echo "<input type='radio' name='quantity' value='6'>6";
	echo "<input type='radio' name='quantity' value='10'>10<br>";
	echo "Print current puzzle: <input type='radio' value='print' name='request'><br>";
	echo "Check current puzzle: <input type='radio' value='check_puzzle' name='request'><br>";
	echo "<input type='submit' value='Enter'><br>";
	echo "-----------------<br>";
}

function enter_data($quantity){
	echo "<form method='post' action=$PHP_SELF>";
	#echo "<form method='post' action='step3_cell.php'>";
	echo "Enter column, row, and value for the cell(s)<br>";
	for ($i=1; $i<=$quantity; $i++) {
	    echo "Column: <input type='text' size='4' name='column[$i]'>";
	    echo "  Row: <input type='text' size='4' name='row[$i]'>";
	    echo "  Value: <input type='text' size='4' name='value[$i]'><br/>";
	}
	echo "  Quantity: <input type='text' size='4' name='quantity' value=$quantity><br/>";
	echo "<input type='submit' name='enter' value='Enter'><br>";


}
function print_puzzle_test(){
    echo "Sudoko Puzzle<br>";
    for ($r=1; $r<=9; $r++) {
	    for ($c=1; $c<=9; $c++) {
		    $id=$r.$c;
		    echo "TP 1  id: ".$id;
	    }
	    echo "<br>";
    }
}


function print_puzzle(){
    echo "Sudoko Puzzle<br>";
    $bg="cccccc";
    echo "<table border='3' cellpadding='10'>";
    for ($r=1; $r<=9; $r++) {
	    echo "<tr>";
	    for ($c=1; $c<=9; $c++) {
		    $id=$r.$c;
		    /* Find the original value for this cell */
		    $result=mysql_query("SELECT orig_value FROM cell
		    WHERE id=$id")
		    or die(mysql_error());
		    $row=mysql_fetch_array($result);
		    $orig_value=$row['orig_value'];

		    $result=mysql_query("SELECT value FROM cell
		    WHERE id=$id")
		    or die(mysql_error());
		    $row=mysql_fetch_array($result);
		    $show=$row['value'];
		    $font="6";
		    if ($orig_value != $show) {
		        /* This is a value which we have determined so
			we will show it in red */
			$color="red";
		    } else {
			$color="black";
		    }
		    if (((($c < 4) && ($r < 4)) || (($c > 6) && ($r < 4))) 
		    || ((($c > 3) && ($r > 3)) && (($c < 7) && ($r < 7))) 
		    || ((($c < 4) && ($r > 6)) || (($c > 6) && ($r > 6)))) {
		    	$bg="ffffff";
		    } else {
		    	$bg="cccccc";
		    }
		    if ($show == 0) {
		    	/* What are the possible values?  */
			$font="3";
			$result=mysql_query("SELECT possible_values FROM cell
			WHERE id=$id")
			or die(mysql_error());
			$row=mysql_fetch_array($result);
			$show=$row['possible_values'];
			$possible_array=split(":", $show);
			$pcount=count($possible_array);
			$hcount=($pcount / 2);
			$show="";
			$counter=0;
			foreach ($possible_array as $p) {
				$counter++;
				#if ($counter >= $hcount) {
				if ($counter > 3) {
				  $counter=0;
				  $show=$show.$p."<br>";
				} else {
				  $show=$show.$p;
				}
			}
		    }
		    #echo "<td bgcolor=#".$bg."><p> <center><font size=".$font."><font color=".$color.">".$show."</p></td>";
		    echo "<td bgcolor=#".$bg."><p><font size=".$font."><font color=".$color.">".$show."</p></td>";
	    }
	    echo "</tr>";
    }
    echo "</table>";
}

function solve_puzzle($directive) {
	/* The rules are:
		There can only be numbers 1 through 9.
		These numbers appear once and only once in each row.
		These numbers appear once and only once in each column.
		These numbers appear once and only once in each sector.

		The $directive is a variable to permit or prevent
		the program from trying the process of elimination 
		to find a solution.  In any case, it will not "guess"
		until it has exhausted its levels of logic.
	*/
	global $main_count;
	global $elimination_count;
	$main_count=0;
	$main_result=main_solution();

	echo "#debug tp D: main_result: $main_result<br>";
	if (($main_result > 9000) && ($elimination_count > 0)) {
	    /* We tried a number during process of elimination and it
	    will not work.  */
	    echo "#debug tp DA restore to known state<br>";
	    echo "#debug elimination count is now: $elimination_count<br>";
	    restore_to_known_state($elimination_count);
	    echo "#debug tp DB elimination count is now: $elimination_count<br>";
	    /* Return to previous process_of_elimination */;
	    return;
	}
	$check_result=check_puzzle(1);
	echo "#debug tp DD: check_result: $check_result<br>";
	SWITCH($check_result){
	    CASE 1111:
		/* This indicates that cells are unsolved. */
		if ($directive) {
		    echo "#debug tp A Cells are unsolved<br>";
		    process_of_elimination();
		} else {
		    echo "Try Guess Mode!<br>";
		}
	        break;

	    CASE 9999:
	        /* This indicates that there are incorrect numbers 
		in some of the cells. */
		if ($elimination_count) {
		    echo "The elimination process of this 
		    program has failed!<br>";
		} else {
		    echo "The logic of this program has failed!<br>";
		}
	        break;

	    CASE 0:
		echo "The puzzle has  been solved!!!<br>";
		print_puzzle();
		menu();
		exit();
	        break;
	
	    DEFAULT:
		echo "Unknown result: $check_result<br>";
	        break;
	}
	print_puzzle();
}

function main_solution(){
	global $main_count;
	$continue=1;
	$result1=1;
	$result2=1;
	$result3=1;
	$result4=1;
	$next_count=0;
	#echo "==> Prep Possible Values <==<br>";
	$prep_result1=prep_possible_values(0);
	if ($prep_result1 > 9000) {
	    echo "Error! Puzzle is invalid!<br>";
	    return $prep_result1;
	} elseif ($prep_result1 == 0) {
	    echo "<br>";
	}
	#echo "==> Inside main_solution <==<br>";
	pdebug( "Result was: ".$prep_result1."<br>");
	while ($continue) {
	  $result1=1;
	  while ($result1) {
	    #echo "==> Stage One <==<br>";
	    $result1=solve_puzzle_stage_one();
	    pdebug( "Result was: ".$result1."<br>");
	    if ($result1 > 9000) {
	      return $result1;
	    }
	    if ($result1) {
	    	$next=0;
		$next_count++;
	    } else {
	    	$next=1;
	    }
	    if ($next) {
	      while ($result2){
		#echo "==> Stage Two <==<br>";
		$result2=solve_puzzle_stage_two();
		if ($result2 > 9000) {
		  echo "#debug tp E result: $result2<br>";
		  return 9999;
		}
		/* If there is only one place in the row, column, or sector 
		where a particular number is possible, 
		even if there are multiple
		possiblities for that cell, that number must be the value. */
		pdebug( "Result was: ".$result2."<br>");
		if ($result2) {
		    $next=0;
		    $next_count++;
		    echo "<br>";
		} else {
		    $next=1;
		}
		if ($next) {
		  while ($result3){
		    #echo "==> Stage Three <==<br>";
		    $result3=solve_puzzle_stage_three();
		    if ($result3 > 9000) {
		      return $result3;
		    }
		    /* If a specific digit is only possible in one row or column
		    in a sector, this means that it cannot be permitted 
		    as a possible digit in that row or column in 
		    other sectors. */
		    pdebug( "Result was: ".$result3."<br>");
		    if ($result3) {
			$next=0;
			$next_count++;
			echo "<br>";
		    } else {
			$next=1;
		    }
		    if ($next) {
			while ($result4) {
			if ($result1 > 9000) {
			  return $result1;
			}
			#echo "==> Stage Four <==<br>";
			$result4=solve_puzzle_stage_four();
			if ($result4) {
			    $next_count++;
			    echo "<br>";
			} 
			/* If there are only two places in the row, 
			column, or sector where two particular numbers 
			are possible, even if there are other
			possiblities for that cell, the possible 
			values must be reduced to 
			these two values. */
			pdebug( "Result was: ".$result4."<br>");
		      } # End of Stage Four
		    }
		  } # End of Stage Three
		} 
	      } # End of Stage Two
	    } 
	  } # End of Stage One

	  $continue=$prep_result1 + $result2 + $result3 + $result4 +$result5;
	  pdebug( "Continue is: ".$continue."<br>");

	  if ($continue > 1000) {
		return $continue;
	  }
	} # End of Continue
	#echo "#debug continue: $continue 
	#next count: $next_count main count: $main_count<br>";
	if ($next_count) {
	    /* This count records how many stages were repeated.  If any
	    were, we should repeat the entire set again until no stages
	    had to run more than once. */
	    $main_count++;
	    if ($main_count > 3) {
	        /* Cannot re-enter main_solution more than 3 times */
	#	echo "Re-entry limit reached<br>";
		return $continue;
	    } else {
		echo "Re-entry to main solution function... <br>";
	    	main_solution();
	    }
	}

	return $continue;
}

function prep_possible_values($level){
	  /* Here we eliminate digits from the list of possible by seeing
	  if that digit already exists in the row, column, or sector. */
	  pdebug("==> Prep Possible Values <==<br>");
    // Sort through the columns
    // $r represents the rows
    for ($r=1; $r<=9; $r++) {
	    // $c represents the columns
	    for ($c=1; $c<=9; $c++) {
		    $id=$r.$c;
		    $result=mysql_query("SELECT value FROM cell
		    WHERE id=$id")
		    or die(mysql_error());
		    $row=mysql_fetch_array($result);
		    $value=$row['value'];
		    // Store this value in the row array
		    SWITCH ($r){
		    	CASE 1:
				$row_1[$c]=$value;
				break;
		    	CASE 2:
				$row_2[$c]=$value;
				break;
		    	CASE 3:
				$row_3[$c]=$value;
				break;
		    	CASE 4:
				$row_4[$c]=$value;
				break;
		    	CASE 5:
				$row_5[$c]=$value;
				break;
		    	CASE 6:
				$row_6[$c]=$value;
				break;
		    	CASE 7:
				$row_7[$c]=$value;
				break;
		    	CASE 8:
				$row_8[$c]=$value;
				break;
		    	CASE 9:
				$row_9[$c]=$value;
				break;
		    }
		    // Store the value in the column array
		    SWITCH ($c){
		    	CASE 1:
				$column_1[$r]=$value;
				break;
		    	CASE 2:
				$column_2[$r]=$value;
				break;
		    	CASE 3:
				$column_3[$r]=$value;
				break;
		    	CASE 4:
				$column_4[$r]=$value;
				break;
		    	CASE 5:
				$column_5[$r]=$value;
				break;
		    	CASE 6:
				$column_6[$r]=$value;
				break;
		    	CASE 7:
				$column_7[$r]=$value;
				break;
		    	CASE 8:
				$column_8[$r]=$value;
				break;
		    	CASE 9:
				$column_9[$r]=$value;
				break;
		    }
	    }
    }
    $array[1]=row;
    $array[2]=column;
    $array[3]=sector;
    foreach ($array as $desc) {
	    /* Solve for row, column and sector */
	    pdebug( "Solve for $desc...<br>");
	    for ($x=1; $x<=9; $x++) {
		    $result=adjust_possible_values($desc,$x);
		    if ($result > 9000) {
		      return $result;
		    }
	    }
    }
    pdebug( "Update values...<br>");
    for ($x=1; $x<=9; $x++) {
	    $result=update_values($x);
    }
    pdebug( "==> END of Prep Possible Values <==<br>");
    return $result;
}

function adjust_possible_values($desc, $index1){
	/* Here we eliminate digits from the list of possible by seeing
	if that digit already exists in the row, column, or sector. */

	$update_count=0;
	$found="";
	$sep="";
	pdebug( "[[ Enter adjust_possible_values ".$desc.$index1."]]<br>");
	for ($index2=1; $index2<=9; $index2++) {
		// Iterate through the described aspect and find the values.
		SWITCH($desc){
		    CASE row:
		    /* Row being represented by index1 and column being
		    represented by index2 */
		    $id=$index1.$index2;
		    $spec="id=".$index1.$index2;
		    break;

		    CASE column:
		    $id=$index2.$index1;
		    $spec="id=".$index2.$index1;
		    break;

		    CASE sector:
		    /* Sector being represented by index1 and the cell
		    within the sector being represented by index2.  
		    Together, they become the sector id. */
		    $id=$index1.$index2;
		    $spec="sector_id=".$index1.$index2;
		    break;
		}
		pdebug( "tp a ".$desc." index1: ".$index1." index2: ".$index2." 
		id: ".$id." spec: ".$spec."<br>");
		$result=mysql_query("SELECT value FROM cell
		WHERE $spec")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$value=$row['value'];

		if ($value != 0) {
			$found=$found.$sep.$value;
			$sep=":";
		}
		pdebug( $desc." tp 1: spec: ".$spec." value: ".$value." 
		values found so far: ".$found."<br>");
	}
	for ($index2=1; $index2<=9; $index2++) {

		// Iterate through the rows and find the possible values
		$new_possible="";
		$n_sep="";
		SWITCH($desc){
		    CASE row:
		    /* Row being represented by index1 and column being
		    represented by index2 */
		    $id=$index1.$index2;
		    $spec="id=".$index1.$index2;
		    break;

		    CASE column:
		    /* Column being represented by index1 and row being
		    represented by index2 */
		    $id=$index2.$index1;
		    $spec="id=".$index2.$index1;
		    break;

		    CASE sector:
		    /* Sector being represented by index1 and the cell
		    within the sector being represented by index2.  
		    Together, they become the sector id. */
		    $id=$index1.$index2;
		    $spec="sector_id=".$index1.$index2;
		    break;
		}

		$result=mysql_query("SELECT possible_values FROM cell
		WHERE $spec")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$possible_values=$row['possible_values'];
		$possible_array=split(":", $possible_values);
		$found_array=split(":", $found);

		/* Compare the possible values to the found values.  That
		is, the values which have been determined for other cells
		in this column, row, or sector (as defined by $desc). 
		If a value has been determined for another
		cell in this column (row, or sector), it cannot be listed 
		as a possible value for any other cell in that column.  
		So, remove it from the list of possible values. */
		pdebug( "".$desc." tp 2: spec: ".$spec." old possible values: 
		".$possible_values."<br>");
		$count_of_possible=0;
		$new_value="";
		foreach ($possible_array as $p) {
			$match=no;
			// pdebug( "".$desc." tp 3 - checking ".$p."<br>");
			foreach ($found_array as $f) {
				if ($p == $f) {
					pdebug( "--->".$desc." tp 4:  ".$p." 
					must be removed from the list of possible.<br>");
					$match=yes;
				}
			}
			if ($match == no) {
				$new_possible=$new_possible.$n_sep.$p;
				$n_sep=":";
				$count_of_possible++;
				$new_value=$p;
			}
		}
		pdebug( "".$desc." tp 5: spec: ".$spec." new possible values: 
		".$new_possible." count of possible values: ".$count_of_possible."<br>");

	
		/* If the number of possible digits has been
		reduced to one, then this is the only possible
		digit and therefore must be the value! */
		if ($possible_values == $new_possible) {
		    pdebug( "===> tp 6 - No update of possible values <br>");
		} else {
		    if (($count_of_possible == 1) && ($new_value != 0)) {
			/* Only one possible value that this
			cell can be. (Remember, the possible values
			field is set to 0 if the value is known so 
			we have to make certain we are not going
			to replace value with 0!) */
			// Update database here.
			pdebug( "===========> tp 6 - Set value to ".$new_value."<br>");
			$update_count++;
			$result=update_database_spec($spec,$new_value);
			if ($result > 9000) {
			  #echo "#debug tp h -adjust_possible_values- result 
			  #spec: $spec new value: $new_value result: $result<br>";
			  return $result;
			}
			/* Once an update is made, break out because
			everything needs to be re-evaluated. */
			break;
		    } else if (($count_of_possible == 1) && ($new_value == 0)) {
			pdebug( "==> tp 6 - possible values is ".$possible_values." 
			so do nothing. <br>");

		    } else {
			/* There are more than one possible
			values left. */
			pdebug("==> tp 6 - Update possible values to 
			".$new_possible."<br>");
			$update_count++;

			// Update database here.
			mysql_query("UPDATE `cell`
			SET possible_values='$new_possible'
			WHERE $spec")
			or die(mysql_error());
		    }
		}
	}
	pdebug( "[[ Leave adjust possible values ".$desc.$index1."]]<br>");
	return $update_count;

}

function pdebug($note){
	#$debug=1;
	#if ($debug == 1) {
	if (DEBUG) {
		echo "#debug: $note";
	} 
}

function solve_one($desc, $index1){
	/* Similar to solve one but if you find two cells in a row,
	column, or sector with only two possible values and these 
	values are the same, then this means that no other cell 
	in this row, column, or sector could have this
	as a possible value so remove it from the list of possible
	values for any other cells in the row, column, or sector. */
	pdebug( "[[ Enter SOLVE ONE  ".$desc.$index1."]]<br>");
pdebug( "----------------PART I ---------------<br>");
	pdebug( "tp a: descriptor: ".$desc." Index: ".$index1."<br>");
	$update_count=0;
	$number_of_pairs=0;
	for ($index2=1; $index2<=9; $index2++) {

		// Iterate through the rows and find the possible values
		// Initialize these values.
		$new_possible="";
		$n_sep="";
		SWITCH($desc){
		    CASE row:
		    /* Row being represented by index1 and column being
		    represented by index2 */
		    $id=$index1.$index2;
		    $spec="id=".$index1.$index2;
		    break;

		    CASE column:
		    /* Column being represented by index1 and row being
		    represented by index2 */
		    $id=$index2.$index1;
		    $spec="id=".$index2.$index1;
		    break;

		    CASE sector:
		    /* Sector being represented by index1 and the cell
		    within the sector being represented by index2.  
		    Together, they become the sector id. */
		    $id=$index1.$index2;
		    $spec="sector_id=".$index1.$index2;
		    break;
		}
		// First get the value.
		$result=mysql_query("SELECT value FROM cell
		WHERE $spec")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$value=$row['value'];

		// Second, get the possible values.
		$result=mysql_query("SELECT possible_values FROM cell
		WHERE $spec")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$possible_values=$row['possible_values'];
		$possible_array=split(":", $possible_values);
		$found_array=split(":", $found);
		$number_of_possible=0;
		foreach ($possible_array as $p) {
			$number_of_possible++;
			}
		/* If there are only two possible values, note this. */
		if (($number_of_possible == 0) || ($p == 0)) {
		  pdebug( "tp b spec: ".$spec." known value: ".$value."<br>");
		} else {
		  pdebug( "tp b spec: ".$spec." possible values: "
		  .$possible_values."<br>");
		}
		if ($number_of_possible == 2) {
			$pairs_of_possible[$index2]=$possible_values;
			$number_of_pairs++;
			pdebug( "tp c --> Index: ".$index2." spec: "
			.$spec." possible values: ".$possible_values.
			" number of pairs: (".$number_of_pairs.")<br>");
		}
	}
	/* Go back through the cells of this descriptor seeing if any cells
	have exactly the same pair of possible values. */
	$allowed_rows="";
	$a_sep="";
	if (($pairs_of_possible) && ($number_of_pairs > 1)) {
	pdebug( "------".$desc."----------PART II ---------------<br>");
	    foreach ($pairs_of_possible as $x=>$values1) {
		SWITCH($desc){
			CASE row:
			CASE sector:
			$cell1=$index1.$x;
			break;

			CASE column:
			$cell1=$x.$index1;
			break;
		}

		pdebug( "Cell1 is ".$cell1." x is ".$x."
		value: ".$values1."<br>");
		$match=no;
		foreach ($pairs_of_possible as $y=>$values2) {
		    SWITCH($desc){
			    CASE row:
			    CASE sector:
			    $cell2=$index1.$y;
			    break;

			    CASE column:
			    $cell2=$y.$index1;
			    break;
		    }
		    pdebug( "Cell2 is ".$cell2." x is ".$y." 
		    possible values: ".$values1."<br>");
		    if (($values1 == $values2) && ($cell1 != $cell2)) {
		    	pdebug( "tp e: --> Cell1 is: ".$cell1."  
			--> Cell2 is: ".$cell2." --> value: ".$values1."<br>");
		    	/* We found a situation where two different cells
			have only two possible values and they are the same
			possible values. The "allowed_desc" variable stores
			which rows, columns, or sectors have these two values. 
			*/
			$allowed_desc=$allowed_desc.$a_sep.$x;

			$match=yes;
			$a_sep=":";
			pdebug( "tp 0 allowed_desc: ".$allowed_desc."<br>");
			/* Call the adjust_possible function and send it
			the column number, the list of the rows where the
			values are permitted and also tell it what these
			values are. */
		    }
		}
	    }
	    if ($match == yes) {
		pdebug( "---going to PART III---<br>");
		$update_count=adjust_possible($desc,$index1,$allowed_desc,$values1);
		if ($update_count > 9000) {
		    #echo "#debug tp g result $result<br>";
		    return $update_count;
		}
	    } 
	}
	pdebug( "[[ Leave SOLVE ONE  ".$desc.$index1."]]<br>");
	return $update_count;

}

function adjust_possible($desc,$index1,$allowed_desc,$values){
	/* For the specified row, column, or sector remove the specified 
	values from the list of possible values for all cells except the
	allowed ones. */
	$update_count=0;
	$allowed_array=split(":", $allowed_desc);
	$values_array=split(":", $values);
	pdebug( "----------------PART III ---------------<br>");
	$a_count=1;
	foreach ($allowed_array as $a) {
	    if ($a_count == 1) {
		$allowed_desc_1=$a;
		$a_count++;
	    } else {
		$allowed_desc_2=$a;
	    }
	}
	pdebug( "Allowed ".$desc."s: ".$allowed_desc_1." 
	and ".$allowed_desc_2."<br>");
	$v_count=1;
	foreach ($values_array as $v) {
	    pdebug( "Allowed value: ".$v."<br>");
	    if ($v_count == 1) {
		$value_1=$v;
	    } else {
		$value_2=$v;
	    }
	    for ($index2=1; $index2<=9; $index2++) {
		SWITCH($desc){
		    CASE row:
		    /* Row being represented by index1 and column being
		    represented by index2 */
		    $id=$index1.$index2;
		    $spec="id=".$index1.$index2;
		    break;

		    CASE column:
		    /* Column being represented by index1 and row being
		    represented by index2 */
		    $id=$index2.$index1;
		    $spec="id=".$index2.$index1;
		    break;

		    CASE sector:
		    /* Sector being represented by index1 and the cell
		    within the sector being represented by index2.  
		    Together, they become the sector id. */
		    $id=$index1.$index2;
		    $spec="sector_id=".$index1.$index2;
		    break;
		}
		if (($index2 != $allowed_desc_1) && 
		($index2 != $allowed_desc_2)) {
		    // Iterate through the cells and find the possible values
		    $new_possible="";
		    $n_sep="";
		    $count_of_possible=0;
		    $new_value="";
		    $result=mysql_query("SELECT possible_values FROM cell
		    WHERE $spec")
		    or die(mysql_error());
		    $row=mysql_fetch_array($result);
		    $possible_values=$row['possible_values'];
		    $possible_array=split(":", $possible_values);
		    /* See if any of the possible values match
		    the values we are looking for.  If they do, then
		    they should not be possible values and we remove
		    them from the list of possible values. */

		    foreach ($possible_array as $p) {
			    $match=no;
			    foreach ($values_array as $v) {
				    if ($p == $v) {
					    $match=yes;
				    }
			    }
			    if (($match == no) && ($p) && ($v)) {
				    $new_possible=$new_possible.$n_sep.$p;
				    $n_sep=":";
				    $count_of_possible++;
				    $new_value=$p;
			    }
		    }
		    if ($new_possible)
		    if ($possible_values == $new_possible) {
		    	/* Just in case we are finding something we 
			have found before. */
			pdebug( "===> tp 6 - No update of possible values <br>");
		    } else {
		      pdebug( "======> tp 6 possible: ".$possible_values."
		      new possible: ".$new_possible." spec: ".$spec."<br>");
		      if ($new_possible) {
			  /* If there are newly defined possible values,
			  set the "possible_values" variable. */
			  $possible_values=$new_possible;
		      } else {
			  /* If there appear to be no possible values,
			  set the variable to 0. */
			  $possible_values=0;
		      }

		      /* Update database here if we have a new
		      list of possible values. */
		      $update_count++;
		      update_possible_spec($spec,$possible_values);

		    }
		}
	    }
	}
	if ($update_count) $prep_result1=prep_possible_values(1);
	if ($prep_result1 > 9000) {
	    #echo  "--------end of  PART III ---------------<br>";
	    return $prep_result1;
	}
	pdebug( "--------end of  PART III ---------------<br>");
	return $update_count;
}

function solve_two($desc, $index1){
   	/* If there is only one place in the row, column, or sector (descriptor)
	where a particular number is possible, even if there are multiple
	possiblities for that cell, that number must be the value. */

	pdebug( "[[ Entering SOLVE TWO ".$desc.$index1."]] <br>");
	$update_count=0;
	// Initialize the array counting the possible digits
	for ($x=1; $x<=9; $x++) {
		$digit[$x]=0;
	}
	for ($index2=1; $index2<=9; $index2++) {
	    SWITCH($desc){
		CASE row:
		/* Row being represented by index1 and column being
		represented by index2 */
		$id=$index1.$index2;
		$spec="id=".$index1.$index2;
		break;

		CASE column:
		/* Column being represented by index1 and row being
		represented by index2 */
		$id=$index2.$index1;
		$spec="id=".$index2.$index1;
		break;

		CASE sector:
		/* Sector being represented by index1 and the cell
		within the sector being represented by index2.  
		Together, they become the sector id. */
		$id=$index1.$index2;
		$spec="sector_id=".$index1.$index2;
		break;
	    }

	    $new_possible="";
	    $n_sep="";
	    $result=mysql_query("SELECT possible_values FROM cell
	    WHERE $spec")
	    or die(mysql_error());
	    $row=mysql_fetch_array($result);
	    $possible_values=$row['possible_values'];
	    $possible_array=split(":", $possible_values);
	    // Now we know all of the possible numbers for this cell.
	    pdebug( "tp 1 spec: ".$spec." possible: ".$possible_values."<br>");
	    for ($x=1; $x<=9; $x++) {
		foreach ($possible_array as $y) {
		    if ($x == $y) {
			$digit[$x]++;
			$cell[$x]=$index2;
		    }
		}
	    }
	}
	for ($x=1; $x<=9; $x++) {
	    if ($digit[$x] == 1) {
		/* This digit was found in only one cell.
		That cell is saved as index2. */
		$index2=$cell[$x];
		SWITCH($desc){
		    CASE row:
		    /* Row being represented by index1 and column being
		    represented by index2 */
		    $id=$index1.$index2;
		    $spec="id=".$index1.$index2;
		    break;

		    CASE column:
		    /* Column being represented by index1 and row being
		    represented by index2 */
		    $id=$index2.$index1;
		    $spec="id=".$index2.$index1;
		    break;

		    CASE sector:
		    /* Sector being represented by index1 and the cell
		    within the sector being represented by index2.  
		    Together, they become the sector id. */
		    $id=$index1.$index2;
		    $spec="sector_id=".$index1.$index2;
		    break;
		}

		pdebug( "==========>tp 2b value: ".$x." 
		is only found in: ".$spec."<br>");

		/* Set the possible values for this cell to $x. */
		// Update database here.
		$result=update_database_spec($spec,$x);
		if ($result > 9000) {
		    echo "#debug tp f result $result<br>";
		    return $result;
		}
		$update_count++;
		/* Once an update is made, break out because
		everything needs to be re-evaluated. */
		break;
	    }
	}

	pdebug( "[[ Leaving SOLVE TWO ".$desc.$index1." ]] <br>");
	return $update_count;
}

function solve_four($desc, $index1){
   	/* If there are only two places in the row, column, or sector 
	where two particular numbers are possible, even if there are other
	possiblities for that cell, the possible values must be reduced to 
	these two values. */
	pdebug( "[[ Entering SOLVE FOUR ".$desc.$index1."]] <br>");

	$update_count=0;
	$digits_with_only_two_locations=array();
	$digit_count=0;
	$cells_array=array();
	// Initialize the array counting the possible digits
	for ($x=1; $x<=9; $x++) {
	    $digit[$x]=0;
	}
	for ($index2=1; $index2<=9; $index2++) {
	    SWITCH($desc){
		CASE row:
		/* Row being represented by index1 and column being
		represented by index2 */
		$id=$index1.$index2;
		$spec="id=".$index1.$index2;
		break;

		CASE column:
		/* Column being represented by index1 and row being
		represented by index2 */
		$id=$index2.$index1;
		$spec="id=".$index2.$index1;
		break;

		CASE sector:
		/* Sector being represented by index1 and the cell
		within the sector being represented by index2.  
		Together, they become the sector id. */
		$id=$index1.$index2;
		$spec="sector_id=".$index1.$index2;
		break;
	    }

	    $new_possible="";
	    $n_sep="";
	    $result=mysql_query("SELECT possible_values FROM cell
	    WHERE $spec")
	    or die(mysql_error());
	    $row=mysql_fetch_array($result);
	    $possible_values=$row['possible_values'];
	    $possible_array=split(":", $possible_values);
	    // Now we know all of the possible numbers for this cell.
	    pdebug( "tp 1 spec: ".$spec." possible: ".$possible_values."<br>");
	    for ($x=1; $x<=9; $x++) {
		$locations_with_digit="";
		$n_sep="";
		foreach ($possible_array as $p) {
		    if ($x == $p) {
			$digit[$x]++;
			/* Get the previous value */
			$locations_with_digit=$cells_array[$x];
			if ($locations_with_digit) $n_sep=":";
			/* Update with the new location */
			$locations_with_digit=$locations_with_digit.$n_sep.$id;
			$cells_array[$x]=$locations_with_digit;
		    }
		}
		/* Cells_array stores the locations where $x is a possible value. */
		pdebug("tp 2a digit: ".$x." found count: ".$digit[$x]."<br>");
		pdebug("tp 2b locations: ".$cells_array[$x]."<br>");
	    } # Go on to the next value for x
	} # Go to the next value for index2

	for ($x=1; $x<=9; $x++) {
	    if ($digit[$x] == 2) {
		/* This digit was found in only two cells. */
		pdebug("tp 3 This digit was found in only two cells: ".$x."<br>");
		array_push($digits_with_only_two_locations, $x);
		$digit_count++;
	    }

	}
	/* We have found digits located in only two cells */
	foreach ($digits_with_only_two_locations as $d) {
	    pdebug("tp 4 The digit is: ".$d."<br>");
	    $locations_with_digit=$cells_array[$d];
	    $locations_array=split(":", $locations_with_digit);
	    $spec1[$d]=$locations_array[0];
	    $spec2[$d]=$locations_array[1];
	    pdebug("tp 4a The digit is: ".$d." The two cells are: ".$spec1[$d]." and ".$spec2[$d]."<br>");
	}
	$update_required=0;
	foreach ($digits_with_only_two_locations as $d){
	    pdebug( "tp 4b  d is: ".$d."<br>");
	    pdebug("tp 4c specs are: ".$spec1[$d]." and ".$spec2[$d]."<br>");
	}
	foreach ($digits_with_only_two_locations as $d){
	    foreach ($digits_with_only_two_locations as $e){
	    if ($d != $e) {
		if (($spec1[$d] == $spec1[$e]) && ($spec2[$d] == $spec2[$e])) {
		    /* We are looking at two different numbers, $d and $e
		    which are found in only two cells $spec1 and $spec2
		    and these two cells are the same two cells. */
		    if ($d <= $e) {
			$first=$d;
			$second=$e;
			$id1=$spec1[$d];
			$id2=$spec2[$d];
		    } else {
			$first=$e;
			$second=$d;
			$id1=$spec1[$d];
			$id2=$spec2[$d];
		    }
		    $n_sep=":";
		    $new_possible=$first.$n_sep.$second;
		    $update_required++;
		    pdebug ("tp 5 The new possible values: "
		    .$new_possible."<br>");
		    pdebug ("tp 6 id1: ".$id1." id2 ".$id2."<br>");
		    }
		}
	    }
	}
	if ($update_required) {
	    pdebug ("=======> tp 6 new possible: ".$new_possible." id1: ".$id1." id2 ".$id2."<br>");
	    SWITCH($desc){
		CASE row:
		$spec1="id=".$id1;
		$spec2="id=".$id2;
		break;

		CASE column:
		$spec1="id=".$id1;
		$spec2="id=".$id2;
		break;

		CASE sector:
		$spec1="sector_id=".$id1;
		$spec2="sector_id=".$id2;
		break;
	    }
	    /* See if the possible_values really needs to be changed. */
	    $result=mysql_query("SELECT possible_values FROM cell
	    WHERE $spec1")
	    or die(mysql_error());
	    $row=mysql_fetch_array($result);
	    $possible_values1=$row['possible_values'];
	    $result=mysql_query("SELECT possible_values FROM cell
	    WHERE $spec2")
	    or die(mysql_error());
	    $row=mysql_fetch_array($result);
	    $possible_values2=$row['possible_values'];

	    $update_required=0;
	    if ($possible_values1 == $new_possible) {
	        pdebug("Possible values do not need to be updated.");
	    } else {
		update_possible_spec($spec1,$new_possible);
		$update_count++;
	    }
	    if ($possible_values2 == $new_possible) {
	        pdebug("Possible values do not need to be updated.");
	    } else {
		update_possible_spec($spec2,$new_possible);
		$update_count++;
	    }
	}

	pdebug( "[[ Leaving SOLVE FOUR ".$desc.$index1." ]] <br>");
	return $update_count;
}

function solve_five($desc,$index1){
	pdebug( "[[ Entering SOLVE FIVE ".$desc.$index1."]] <br>");
	/* Here we look for a cell with only two possible values */
	for ($index2=1; $index2<=9; $index2++){
	    SWITCH($desc) {
		CASE row:
		$spec="id=".$index1.$index2;
		break;
		CASE column:
		$spec="id=".$index2.$index1;
		break;
		CASE sector:
		$spec="sector_id=".$index1.$index2;
		break;
	    }
	}
	pdebug( "[[ Leaving SOLVE FIVE ".$desc.$index1." ]] <br>");
	return $update_count;
}

function solve_puzzle_stage_one(){
    $result=0;
    $array[1]=row;
    $array[2]=column;
    $array[3]=sector;

    foreach ($array as $desc) {
	    /* Solve for row, column and sector */

	    pdebug( "Solve for $desc...<br>");
	    $result_sum=0;
	    for ($x=1; $x<=9; $x++) {
		    $result+=solve_one($desc,$x);
		    if ($result > 9000) return $result;
	    }
    }
    return $result;
}

function solve_puzzle_stage_two(){
    $result=0;
    $array[1]=row;
    $array[2]=column;
    $array[3]=sector;
    foreach ($array as $desc) {
	    /* Solve for row, column and sector */
	    pdebug( "Solve for $desc...<br>");
	    for ($x=1; $x<=9; $x++) {
		    $result+=solve_two($desc,$x);
		    if ($result > 9000) return $result;
	    }
    }
    return $result;
}

function solve_puzzle_stage_four(){
    $result=0;
    #$array[1]=row;
    $array[2]=column;
    #$array[3]=sector;
    foreach ($array as $desc) {
	    /* Solve for row, column and sector */
	    pdebug( "Solve for $desc...<br>");
	    for ($x=1; $x<=9; $x++) {
		    $result+=solve_four($desc,$x);
		    if ($result > 9000) return $result;
	    }
    }
    return $result;
}

function process_of_elimination(){
	/* If we get this far, the only way I know to solve the puzzle
	is to guess.  So, we look for a cell which has only two possible values.
	We record which cell that is and copy the current values into 
	the known_good field so we can restore it if necessary.
	*/
	pdebug("Process of elimination<br>");
	global $guessing;
	global $tried_cells;
	global $elimination_count;
	global $known_good_value;
	global $known_good_possible_values;
	global $elimination_count;
	if (IsSet($elimination_count)) {
	    $elimination_count++;
	} else {
	    $elimination_count=1;
	}
	echo "Process of elimination - count: $elimination_count<br>";
	if ($elimination_count > 7) {
		print_puzzle();
		die("The elimination function has been entered 
		$elimination_count times.<br>"); 
	}
	$guessing=1;
	$cells_with_two_possible=array();
	for ($index1=1; $index1<=9; $index1++) {
	    /* Iterate through the rows and record the current value
	    and possible values for each cell. */
	    $result=mysql_query("SELECT id, value, possible_values FROM `cell`
	    WHERE row_name=$index1")
	    or die(mysql_error());
	    while($row=mysql_fetch_array($result)){
		$cell_id=$row['id'];
		$value=$row['value'];
		$possible_values=$row['possible_values'];
		$known_good_value[$cell_id][$elimination_count]=$value;
		$known_good_possible_values[$cell_id][$elimination_count]=$possible_values;
		$possible_array=split(":", $possible_values);
		if (count($possible_array) == 2) {
		    /* This is a cell with only two possible values. */
		    array_push($cells_with_two_possible, $cell_id);
		}
	    }
	}

	/* Now we have a record of how the puzzle looked up to now so we
	can restore it to this point if necessary. */

	/* What we are going to do is to start with the first cell that has
	only two possible values.  We will guess that the value should be one
	of those two.  Then, we try to solve the puzzle the rest of the way.

	If it turns out to be wrong, we need to restore and try 
	something different.  However, if it simply fails to complete, we
	need to try this same thing over again. */

	foreach ($cells_with_two_possible as $trial_cell) {
	    $possible_values=$known_good_possible_values[$trial_cell][$elimination_count];
	    $possible_values_array=split(":", $possible_values);
	    $first_possible_value=$possible_values_array[0];
	    /* Now we have determined the two possible values of the first cell
	    we found with only two possible values.  Let's try out that value
	    and see what happens. */

	    echo "Trial cell: $trial_cell - possible values: ";
	    foreach ($possible_values_array as $p) {
	    	echo "$p ";
	    }
	    echo "<br>";
	    foreach ($possible_values_array as $possible_value){
		echo "#debug tp A - cell: $trial_cell value: 
		$possible_value<br>";
		mysql_query("UPDATE `cell`
		SET value=$possible_value
		WHERE id=$trial_cell")
		or die(mysql_error());

		mysql_query("UPDATE `cell`
		SET possible_values=0
		WHERE id=$trial_cell")
		or die(mysql_error());

		/* Re-enter solve_puzzle */
		echo "#debug tp C calling solve_puzzle<br>";
		solve_puzzle(1);
	    }

	}
}

function restore_to_known_state($elimination_count) {
	/* Read the known good values back into the database. */
	pdebug("Restore to known state<br>");
	echo "Restore to known state - count $elimination_count<br>";
	global $elimination_count;
	global $known_good_value;
	global $known_good_possible_values;

	foreach ($known_good_value as $cell_id=>$key) {
	  foreach ($key as $ecount=>$good_value) {
	    /* Iterate through the rows and restore the known good value
	    and possible values for each cell. */
	    if ($ecount == $elimination_count) {
	      pdebug("Restoring known good value for cell $cell_id <br>");
	      #echo "Restoring known good value for cell $cell_id <br>";
	      #echo "==> Value: $known_good_value[$cell_id]<br>";
	      mysql_query("UPDATE `cell`
	      SET value=$good_value
	      WHERE id=$cell_id")
	      or die(mysql_error());
	    }
	  }
	}
	foreach ($known_good_possible_values as $cell_id=>$key) {
	  foreach ($key as $ecount=>$good_possible_values) {
	    if ($ecount == $elimination_count) {

	      pdebug("Restoring known good possible values 
	      for cell $cell_id <br>");

	      mysql_query("UPDATE `cell`
	      SET possible_values='$good_possible_values'
	      WHERE id=$cell_id")
	      or die(mysql_error());
	    }
	  }
	}
	/* Decrement the elimination count. */
	$elimination_count--;

}

function solve_puzzle_stage_three(){
	pdebug("[[ Entering STAGE THREE ]]<br>");
	/* If a specific digit is only possible in one row or column
	in a sector, this means that it cannot be permitted as a possible
	digit in that row or column in other sectors. */
	/* Iterate through the sector loc values */
	$update_count=0;
	for ($index1=1; $index1<=3; $index1++){
	  for ($index2=1; $index2<=3; $index2++){
	      /* Get the possible cells for the sector */
	      $cell_array=array();
	      $sector_loc=$index1.$index2;

	      $spec="sector_loc=".$sector_loc;

	      pdebug("We are in sector: ".$sector_loc."<br>");
	      $result=mysql_query("SELECT id FROM `cell`
	      WHERE $spec")
	      or die(mysql_error());
	      while($row=mysql_fetch_array($result)){
		  array_push($cell_array, $row['id']);
	      }
	      pdebug( "spec: ".$spec." cells: ".$c."<br>");
	      /* Iterate through the digits */
	      for ($digit=1; $digit<=9; $digit++) {
		  pdebug("<br>------ examining -->digit: ".$digit."<br>");
		  $digit_r=array();
		  $digit_c=array();
		  foreach ($cell_array as $c) {
		      pdebug( "cell: ".$c);
		      $spec="id=".$c;

		      $result=mysql_query("SELECT row_name,
		      column_name, value, possible_values 
		      FROM cell
		      WHERE $spec")
		      or die(mysql_error());
		      $row=mysql_fetch_array($result);
		      $possible_values=$row['possible_values'];
		      $value=$row['value'];
		      $row_name=$row['row_name'];
		      $column_name=$row['column_name'];
		      $possible_array=split(":", $possible_values);
		      pdebug( " value: ".$value." possible_values:
		       ".$possible_values."<br>");
		      foreach ($possible_array as $p) {
			if ($digit == $p) {
			    pdebug( "========>spec: ".$spec." row: ".$row_name." column: ".$column_name."<br>");
			    $digit_r[$row_name]++;
			    $digit_c[$column_name]++;
			}
		      }
		  }
		  pdebug( "--------==>Rows: <br>");
		  $row_counter=0;
		  foreach ($digit_r as $d=>$key){
		    pdebug( "digit: ".$digit." row: ".$d." count: ".$key."<br>");
		    $row_counter++;
		    $row_found_in=$d;
		  }
		  pdebug( "--------==>Columns: <br>");
		  $column_counter=0;
		  foreach ($digit_c as $d=>$key){
		    pdebug( "digit: ".$digit." column: ".$d." count: ".$key."<br>");
		    $column_counter++;
		    $column_found_in=$d;
		  }
		  // PROCESS ROWS
		  if ($row_counter == 1) {
		    pdebug( "+++++++ DIGIT ".$digit." is only found 
		    ".$row_counter." time. That is row: 
		    ".$row_found_in."<br>");
		    pdebug("We are in sector: ".$sector_loc."<br>");
		    /* Now, get all of the cells in this row.  Then,
		    remove this digit from the list of possible for 
		    all cells except those in this sector. */

		    $cell_array=array();
		    $result=mysql_query("SELECT id, possible_values, sector_loc
		    FROM cell
		    WHERE row_name=$row_found_in")
		    or die(mysql_error());
		    while($row=mysql_fetch_array($result)){
		      $found_cell=$row['id'];
		      $possible_values=$row['possible_values'];
		      $found_sector_loc=$row['sector_loc'];
		      if ($found_sector_loc != $sector_loc){
		        /* We have found a cell in this row
			which is not in the same sector.  We
			want to remove this digit from the list
			of possible values for this cell. */
			pdebug("Found Cell: ".$found_cell."<br>");
			$new_possible="";
			$n_sep="";
			$count_of_possible=0;
			$new_value="";
			$possible_array=split(":", $possible_values);
			/* See if any of the possible values match
			the digit we are looking for.  If they do, then
			the digit should be removed from the list of
			possible values. */

			foreach ($possible_array as $p) {
			    $match=no;
			    if ($p == $digit) {
				pdebug("===>Found<=== ".$p."<br>");
				$match=yes;
			    }
			    if (($match == no) && ($p) && ($digit)) {
				$new_possible=$new_possible.$n_sep.$p;
				$n_sep=":";
				$count_of_possible++;
				$new_value="p";
			    }
			}
			/* Update the database with the new list of
			possible digits. */
			/* If the number of possible digits has been
			reduced to one, then this is the only possible
			digit and therefore must be the value! */
			if ($possible_values == $new_possible) {
			    pdebug( "===> tp 6 - No update of possible values <br>");
			} else {
			    if (($count_of_possible == 1) && ($new_value != 0)) {
				/* Only one possible value that this
				cell can be. (Remember, the possible values
				field is set to 0 if the value is known so 
				we have to make certain we are not going
				to replace value with 0!) */
				// Update database here.
				pdebug( "===========> tp 6 - Set value to ".$new_value."<br>");
				$update_count++;
				$result=update_database_spec($spec,$new_value);
				if ($result > 9000) {
				    #echo "#debug tp a result $result<br>";
				    return $result;
				}
				/* Once an update is made, break out because
				everything needs to be re-evaluated. */
				break;
			    } else if (($count_of_possible == 1) && ($new_value == 0)) {
				pdebug( "==> tp 6 - possible values is ".$possible_values." so do nothing. <br>");

			    } else if ($count_of_possible >= 1) {
				/* There are more than one possible
				values left. */
				pdebug("==> tp 6 - Update possible values to 
				$update_count++;
				".$new_possible."<br>");

				// Update database here.
				$spec="id=".$found_cell;
				update_possible_spec($spec,$new_possible);
			    }
			}
		      }
		    }
		  }
		  // PROCESS COLUMNS
		  if ($column_counter == 1) {
		    pdebug( "+++++++ DIGIT ".$digit." is only found 
		    ".$column_counter." time. That is column: 
		    ".$column_found_in."<br>");
		    pdebug("We are in sector: ".$sector_loc."<br>");
		    /* Now, get all of the cells in this column.  Then,
		    remove this digit from the list of possible for 
		    all cells except those in this sector. */

		    $cell_array=array();
		    $result=mysql_query("SELECT id, possible_values, sector_loc
		    FROM cell
		    WHERE column_name=$column_found_in")
		    or die(mysql_error());
		    while($row=mysql_fetch_array($result)){
		      $found_cell=$row['id'];
		      $possible_values=$row['possible_values'];
		      $found_sector_loc=$row['sector_loc'];
		      if ($found_sector_loc != $sector_loc){
			pdebug("Found sector in: ".$found_sector_loc."<br>");
		        /* We have found a cell in this column
			which is not in the same sector.  We
			want to remove this digit from the list
			of possible values for this cell. */
			pdebug("Found Cell: ".$found_cell."<br>");
			$new_possible="";
			$n_sep="";
			$count_of_possible=0;
			$new_value="";
			$possible_array=split(":", $possible_values);
			/* See if any of the possible values match
			the digit we are looking for.  If they do, then
			the digit should be removed from the list of
			possible values. */

			foreach ($possible_array as $p) {
			    $match=no;
			    pdebug("tp 7 - possible value: ".$p."<br>");
			    if ($p == $digit) {
				pdebug("===>Found<=== ".$p."<br>");
				$match=yes;
			    }
			    if (($match == no) && ($p) && ($digit)) {
				$new_possible=$new_possible.$n_sep.$p;
				$n_sep=":";
				$count_of_possible++;
				$new_value="$p";
				pdebug("tp 7 - new possible values: ".$new_possible."<br>");
#				echo "#debug tp 7 - new possible values: ".$new_possible."<br>";
#				echo "#debug tp 7 - new value: ".$new_value."<br>";
				#exit();
			    }
			}
			/* Update the database with the new list of
			possible digits. */
			/* If the number of possible digits has been
			reduced to one, then this is the only possible
			digit and therefore must be the value! */
			if ($possible_values == $new_possible) {
			    pdebug( "===> tp 6 - No update of possible values - old possible values: ".$possible_values."<br>");
			} else {
			    if (($count_of_possible == 1) && ($new_value != 0)) {
				/* Only one possible value that this
				cell can be. (Remember, the possible values
				field is set to 0 if the value is known so 
				we have to make certain we are not going
				to replace value with 0!) */
				// Update database here.
				pdebug( "===========> tp 6 - Set value to ".$new_value."<br>");
				$update_count++;
				$result=update_database_spec($spec,$new_value);
				if ($result > 9000) {
				    #echo "#debug tp b result $result<br>";
				    return $result;
				}
				/* Once an update is made, break out because
				everything needs to be re-evaluated. */
				break;
			    } else if (($count_of_possible == 1) && ($new_value == 0)) {
				pdebug( "==> tp 6 - possible values is ".$possible_values." so do nothing. <br>");

			    } else if ($count_of_possible >= 1) {
				/* There are more than one possible
				values left. */
				$spec="id=".$found_cell;
				pdebug("==> tp 6 - Update possible values to 
				".$new_possible."<br>");
				$update_count++;
				// Update database here.
				update_possible_spec($spec,$new_possible);
			    }
			}
		      }
		    }
		  }
	      }
	  } 
	} 
	pdebug("[[ Leaving STAGE THREE ]]<br>");
	return $update_count;

}

function update_values($column) {
	/* Now that we have updated the possible values, update the known values.
	That is, if there is only one possible value, then this should be
	the known value.
	*/
	$new_value_count=0;
	for ($row=1; $row<=9; $row++) {

		/* Iterate through the cells and find the possible values.
		Look for cells which have only one possible value.
		*/
		$id=$row.$column;
		$result=mysql_query("SELECT possible_values 
		FROM cell
		WHERE id=$id")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$possible_values=$row['possible_values'];
		$possible_array=split(":", $possible_values);
		$count_possible=0;
		$new_value="";
		foreach ($possible_array as $p) {
			if ($p != 0){
				// Count this
				$count_possible++;
				// Grab the value
				$new_value=$p;
			}
		}
		if ($count_possible == 1) {
			/* This is the only possible answer for this cell
			so add the value to the database.
			*/
			// Update database here.
			$new_value_count++;
			pdebug( "++++++++++++++++++>Updating ".$id." to ".$new_value."<br>");
			$spec="id=".$id;
			$result=update_database_spec($spec,$new_value);
			if ($result > 9000) {
			    #echo "#debug tp c result $result<br>";
			    return $result;
			}
			/* Once an update is made, break out because
			everything needs to be re-evaluated. */
			break;
		}
	}
	return $new_value_count;
}


function update_database($column, $row, $value){
	/* This function will update the value for the designated cell
	but before we make the update, lets make certain that the value
	seems reasonable.  Remember, the value can appear only once
	in a row or column. */

	$id=$row.$column;
	/* First, search through the rows */
	$result=check_permitted(row, $row, $value);
	/* Then, search through the columns */
	$result=check_permitted(column, $column, $value);
	/* Then, search through the cells in this sector */
	/* First, determine what sector it is */

	$result=mysql_query("SELECT sector_loc
	FROM cell
	WHERE id=$id")
	or die(mysql_error());
	$row=mysql_fetch_array($result);
	$sector_loc=$row['sector_loc'];
	$result=check_permitted(sector, $sector_loc, $value);
	if ($result > 9000) {
	    return $result;
	}


	pdebug( "id: ".$id." value: ".$value."<br>");
	mysql_query("UPDATE `cell`
	SET value=$value
	WHERE id=$id")
	or die(mysql_error());
	mysql_query("UPDATE `cell`
	SET possible_values=0
	WHERE id=$id")
	or die(mysql_error());
	echo "Selection: A ".$cell." Value: ".$value."<br>";
	$enter="Entered";
}

function check_permitted($desc,$index1,$value){
	pdebug( "tp 0 desc: ".$desc." index: ".$index1." 
	value: ".$value."<br>");
	if($desc == sector) {
	    /* If it is a sector we are checking, we do it this way... */
	    pdebug("tp 8s - checking ".$desc." sector_loc: "
	    .$index1." value: ".$value."<br>");

	    $result=mysql_query("SELECT value
	    FROM cell
	    WHERE sector_loc=$index1")
	    or die(mysql_error());
	    while ($row=mysql_fetch_array($result)) {
		$found_value=$row['value'];
		if ($value == $found_value) {
		    /* Whoa there!  You cannot insert $value into $desc
		    $index1 because another cell in this $desc has 
		    this value! */
		    pdebug("Duplicate value! Cannot put value $value in sector_loc $index.<br>");
		    echo "#debug tp A Duplicate value! Cannot put value $value in sector_loc $index.<br>";
		    return 9999;
		}
	    }
	} else {
	    for ($index2=1; $index2<=9; $index2++) {
	        if ($index2 != $index1){
		    SWITCH($desc){
			CASE row:
			/* Row being represented by index1 and column being
			represented by index2 */
			$id=$index1.$index2;
			$spec="id=".$id;
			break;
			CASE column:
			/* Column being represented by index1 and column being
			represented by index2 */
			$id=$index2.$index1;
			$spec="id=".$id;
			break;
		    }
		    pdebug("tp 8 - checking ".$desc." spec: "
		    .$spec." value: ".$value."<br>");

		    $result=mysql_query("SELECT value
		    FROM cell
		    WHERE $spec")
		    or die(mysql_error());
		    $row=mysql_fetch_array($result);
		    $found_value=$row['value'];
		    pdebug("tp 8a - spec: ".$spec." found value: "
		    .$found_value."<br>");
		    if ($value == $found_value) {
			/* Whoa there!  You cannot insert $value into 
			$row because another cell in this row has this value! */
			pdebug("Cannot put value $value in cell $id - 
			Duplicate value in cell $spec.<br>");
			return 9999;
		    }
		}

	    }
	}
	pdebug( "leaving tp 0 desc: ".$desc." index: ".$index1." 
	value: ".$value."<br>");
}

function update_database_spec($spec,$value){
	/* Update a value in the database according to specified cell "spec." */
	$result=check_permitted_spec($spec,$value);
	if ($result > 9000) {
	    return $result;
	}

	pdebug( "update_database spec: ".$spec." value: ".$value."<br>");
	#echo "Selection: B ".$spec." Value: ".$value."<br>";
	#echo ".";
	#todo
	print_puzzle();
	mysql_query("UPDATE `cell`
	SET value=$value
	WHERE $spec")
	or die(mysql_error());
	mysql_query("UPDATE `cell`
	SET possible_values=0
	WHERE $spec")
	or die(mysql_error());
	pdebug("Selection: ".$spec." Value: ".$value."<br>");
	pdebug("Added to database.<br>");
	if (STOP) {
	  #print_puzzle();
	  menu();
	  exit();
	}
	$prep_result1=prep_possible_values(2);
	if ($prep_result1 > 9000) {
	    #echo  "--------end of  update_database_spec
	    #spec: $spec value: $value ---------------<br>";
	    return $prep_result1;
	}
	return $prep_result1;
}

function check_permitted_spec($spec,$value) {
	pdebug( "tp 1 check_permitted_spec ".$spec." value ".$value."<br>");
	/* From the spec passed in, determine the row, column, and sector */
	$result=mysql_query("SELECT row_name,
	column_name, sector_loc 
	FROM cell
	WHERE $spec")
	or die(mysql_error());
	$row=mysql_fetch_array($result);
	$row_name=$row['row_name'];
	$column_name=$row['column_name'];
	$sector_loc=$row['sector_loc'];
	/* First, search through the rows */
	$result=check_permitted(row, $row_name, $value);
	if ($result > 9000) {
	    #echo "#debug - AA check_permitted_spec: result: $result<br>";
	    return $result;
	}
	/* Then, search through the columns */
	$result=check_permitted(column, $column_name, $value);
	if ($result > 9000) {
	    #echo "#debug - AA check_permitted_spec: result: $result<br>";
	    return $result;
	}
	/* Then, search through the cells in this sector */
	$result=check_permitted(sector, $sector_loc, $value);
	if ($result > 9000) {
	    #echo "#debug - AA check_permitted_spec: result: $result<br>";
	    return $result;
	}
	return $result;

}

function update_possible_spec($spec,$possible_values){
	pdebug("tp 7 - inside update. spec: ".$spec." values: ".$possible_values."<br>");
	mysql_query("UPDATE `cell`
	SET possible_values='$possible_values'
	WHERE $spec")
	or die(mysql_error());
	$prep_result1=prep_possible_values(3);
	if ($prep_result1 > 9000) {
	    return $prep_result1;
	}
	return $prep_result1;
}

function reset_data() {
	/* Set value and possible values to original */
	for($index1=1; $index1<=9; $index1++){
	    for($index2=1; $index2<=9; $index2++){
		$id=$index1.$index2;
		$spec="id=".$index1.$index2;

		/* Get the original values */
		$result=mysql_query("SELECT orig_value,
		orig_possible_values 
		FROM cell
		WHERE $spec")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$orig_possible_values=$row['orig_possible_values'];
		$orig_value=$row['orig_value'];

		/* Set value and original_values */
		mysql_query("UPDATE `cell`
		SET value=$orig_value
		WHERE $spec")
		or die(mysql_error());

		mysql_query("UPDATE `cell`
		SET possible_values='$orig_possible_values'
		WHERE $spec")
		or die(mysql_error());
	    }
	}
}

function check_puzzle($verbose){
	/* Check to see if the puzzle is complete */
	$array[1]=row;
	$array[2]=column;
	$array[3]=sector;

	foreach ($array as $desc) {
		/* Solve for row, column and sector */

		pdebug( "Check for $desc...<br>");
		$result=0;
		for ($x=1; $x<=9; $x++) {
			$result=check_digits($desc,$x,$verbose);
			if ($result) {
			  return $result;
			}
		}
	}
	return $result;

}

function check_digits($desc,$index1,$verbose){
	/* Check that we have all of the digits from 1 to 9
	and none twice. */
	$error_code=0;
	$found_array=array();
	for ($x=1; $x<=9; $x++){
	  /* Initialize array for counting digits found. */
	  $found_array[$x]=0;
	}
	/* The lesser error is if a cell has not been solved.  This
	means that a cell still has a value of 0.  The error code
	is 1111. */
	for ($index2=1; $index2<=9; $index2++){
		SWITCH($desc) {
		    CASE row:
		    $spec="id=".$index1.$index2;
		    break;
		    CASE column:
		    $spec="id=".$index2.$index1;
		    break;
		    CASE sector:
		    $spec="sector_id=".$index1.$index2;
		    break;
		}

		/* Get the current value */
		$result=mysql_query("SELECT value
		FROM cell
		WHERE $spec")
		or die(mysql_error());
		$row=mysql_fetch_array($result);
		$found_value=$row['value'];
		/* Record by incrementing the array */
		if ($found_value == 0) {
			/* This means we have a cell we have not
			solved for.  We need not proceed but can
			stop here. */
			if ($verbose){
			    echo "Zero found in $spec!<br>";
			}
			echo "#debug tp B Zero found in $spec!<br>";
			$error_code=1111;
			/* It does not matter if we found one or twenty
			cells with a zero.  */
			break;
		}
		$found_array[$found_value]++;

	}
	/* The more serious error is if numbers are incorrect.  If this
	is found, the error code is 9999.  Note that if 0's were found
	previously, this will over-write the error code of 1111 with
	9999. */
	for ($x=1; $x<=9; $x++){
	    /* Iterate through the digit range */
	    if ($error_code == 1111) {
		/* We know some cells will have zeros so look to see
		if some numbers are present more than once. */
		if ($found_array[$x] > 1){
		    #$errors_found++;
		    $error_code=9999;
		    if ($verbose){
			echo "$desc $index1 - Found $found_array[$x] number $x!<br>";
		    }
		}
	    } else {
		/* We found no cells with Zero's so each number
		should be present only once. */
		if ($found_array[$x] != 1){
		    #$errors_found++;
		    $error_code=9999;
		    if ($verbose){
			echo "$desc $index1 - Found $found_array[$x] number $x!<br>";
		    }
		}
	    }
	}
	return $error_code;
	/*
	if ($errors_found) {
	    echo "#debug tp C Errors found ";
	    echo "$desc $index1 - Found $found_array[$x] number $x!<br>";
	    return 9999;
	} else {
	    return 0;
	}
	*/
}

function draw_input_puzzle() {

	// Draw a 9 x 9 grid, accept input and pass it to step2 script.
	$bg="cccccc";
	$font="8";
	echo "<table border='3'>";
	echo "Enter known values:<br>";
	echo "<form method='post' action=$PHP_SELF>";
	echo "<input type='text' size='3'maxlength='1'name='cell_11'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_12'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_13'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_14'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_15'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_16'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_17'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_18'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_19'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_21'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_22'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_23'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_24'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_25'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_26'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_27'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_28'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_39'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_31'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_32'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_33'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_34'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_35'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_36'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_37'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_38'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_39'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_41'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_42'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_43'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_44'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_45'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_46'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_47'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_48'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_49'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_51'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_52'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_53'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_54'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_55'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_56'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_57'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_58'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_59'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_61'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_62'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_63'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_64'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_65'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_66'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_67'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_68'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_69'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_71'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_72'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_73'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_74'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_75'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_76'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_77'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_78'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_79'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_81'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_82'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_83'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_84'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_85'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_86'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_87'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_88'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_89'><br>";
	echo "<input type='text' size='3'maxlength='1'name='cell_91'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_92'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_93'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_94'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_95'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_96'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_97'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_98'>";
	echo "<input type='text' size='3'maxlength='1'name='cell_99'><br>";
	echo "<input type='submit' name='request' value='fill_database'><br>";
	echo "</form>";
}


function fill_puzzle_database(){
	/* Take the data input and enter it into the database. */
	echo "Sudoko Puzzle<br>";
	for ($r=1; $r<=9; $r++) {
		$row_name=$r;
		for ($c=1; $c<=9; $c++) {
			$column_name=$c;
			$location=$r.$c;
			$cell="cell_".$location;
			if ($$cell=$_POST[$cell]){
			  $$cell=$_POST[$cell];
			} else {
			  $$cell="0";
			}
			$val=$$cell;
			$possible_values="";
			$sep="";
			for ($p=1; $p<=9; $p++) {
				//if ($p != $val) {
				if ($val == '0') {
					$possible_values=$possible_values.$sep.$p;
					$sep=":";
				} else {
					$possible_values=0;
				}
			}
			/* Each cell is in a sector.  A "sector" is a 3x3 grid of cells.
			These sectors are numbered 1-9 from left to right and top to 
			bottom.  Within each sector, there are 9 cells which are also 
			numbered from left to right and top to bottom.  Each cell is
			given a sector id composed of its sector number and its cell
			number within that sector.

			So, for example, the top left sector on the overall Sudoku puzzle
			is rows 1 through 3 and columns 1 through 3.  This is sector 
			number 1.  Sector number 2 is rows 1 through 3 and columns 4 
			through 5.

			The id of the first cell of the first sector is 1,1 (row 1 and
			column 1) and its sector id is also 1,1 (sector 1 cell 1).

			The first cell in the second sector has the id of 1,3 (row 1
			and column 3) with a sector id of 2,1 (sector 2 and cell 1.)

			I am using a switch to correlate the cell id to the sector id.
			*/
			$sector_id=get_sector_id($location);

			pdebug(" Cell: ".$location." value: ".$val." ");
			pdebug( " Row: ".$row_name." Column: ".$column_name." ");
			pdebug( " Possible values: ".$possible_values." ");
			pdebug( " Sector: ".$sector_id."<br>");

			mysql_query("REPLACE INTO `cell`
			(id, value, orig_value, orig_possible_values, possible_values, row_name, column_name, sector_id) VALUES ($location, $val, $val, '$possible_values', '$possible_values', $row_name, $column_name, '$sector_id')")
			or die(mysql_error());

		}
	}
}

function get_sector_id($id){
	SWITCH($id) {
		case (11):
		case (12):
		case (13):
			$sector_id=$id;
			break;
		case (14):
			$sector_id=21;
			break;
		case (15):
			$sector_id=22;
			break;
		case (16):
			$sector_id=23;
			break;
		case (17):
			$sector_id=31;
			break;
		case (18):
			$sector_id=32;
			break;
		case (19):
			$sector_id=33;
			break;
		case (21):
			$sector_id=14;
			break;
		case (22):
			$sector_id=15;
			break;
		case (23):
			$sector_id=16;
			break;
		case (24):
			$sector_id=24;
			break;
		case (25):
			$sector_id=25;
			break;
		case (26):
			$sector_id=26;
			break;
		case (27):
			$sector_id=34;
			break;
		case (28):
			$sector_id=35;
			break;
		case (29):
			$sector_id=36;
			break;
		case (31):
			$sector_id=17;
			break;
		case (32):
			$sector_id=18;
			break;
		case (33):
			$sector_id=19;
			break;
		case (34):
			$sector_id=27;
			break;
		case (35):
			$sector_id=28;
			break;
		case (36):
			$sector_id=29;
			break;
		case (37):
			$sector_id=37;
			break;
		case (38):
			$sector_id=38;
			break;
		case (39):
			$sector_id=39;
			break;
		case (41):
		case (42):
		case (43):
			$sector_id=$id;
			break;
		case (44):
			$sector_id=51;
			break;
		case (45):
			$sector_id=52;
			break;
		case (46):
			$sector_id=53;
			break;
		case (47):
			$sector_id=61;
			break;
		case (48):
			$sector_id=62;
			break;
		case (49):
			$sector_id=63;
			break;
		case (51):
			$sector_id=44;
			break;
		case (52):
			$sector_id=45;
			break;
		case (53):
			$sector_id=46;
			break;
		case (54):
			$sector_id=54;
			break;
		case (55):
			$sector_id=55;
			break;
		case (56):
			$sector_id=56;
			break;
		case (57):
			$sector_id=64;
			break;
		case (58):
			$sector_id=65;
			break;
		case (59):
			$sector_id=66;
			break;
		case (61):
			$sector_id=47;
			break;
		case (62):
			$sector_id=48;
			break;
		case (63):
			$sector_id=49;
			break;
		case (64):
			$sector_id=57;
			break;
		case (65):
			$sector_id=58;
			break;
		case (66):
			$sector_id=59;
			break;
		case (67):
			$sector_id=67;
			break;
		case (68):
			$sector_id=68;
			break;
		case (69):
			$sector_id=69;
			break;
		case (71):
		case (72):
		case (73):
			$sector_id=$id;
			break;
		case (74):
			$sector_id=81;
			break;
		case (75):
			$sector_id=82;
			break;
		case (76):
			$sector_id=83;
			break;
		case (77):
			$sector_id=91;
			break;
		case (78):
			$sector_id=92;
			break;
		case (79):
			$sector_id=93;
			break;
		case (81):
			$sector_id=74;
			break;
		case (82):
			$sector_id=75;
			break;
		case (83):
			$sector_id=76;
			break;
		case (84):
			$sector_id=84;
			break;
		case (85):
			$sector_id=85;
			break;
		case (86):
			$sector_id=86;
			break;
		case (87):
			$sector_id=94;
			break;
		case (88):
			$sector_id=95;
			break;
		case (89):
			$sector_id=96;
			break;
		case (91):
			$sector_id=77;
			break;
		case (92):
			$sector_id=78;
			break;
		case (93):
			$sector_id=79;
			break;
		case (94):
			$sector_id=87;
			break;
		case (95):
			$sector_id=88;
			break;
		case (96):
			$sector_id=89;
			break;
		case (97):
			$sector_id=97;
			break;
		case (98):
			$sector_id=98;
			break;
		case (99):
			$sector_id=99;
			break;

	}
	pdebug( "Returning (".$sector_id.") for id of (".$id.")<br>");
	return $sector_id;
}


function sector_prep(){
    /* Just as each cell has an id made from the column
    and the row number, each sector has an id called
    "sector_loc" (sector location) made from which of the
    three sector columns and which of the three sector
    rows it is in. Based on the id, we determine what
    the sector_loc is. */
    $array[1]=row;
    $array[2]=column;
    foreach ($array as $desc) {
      /* Solve for row, and column */
      pdebug( "Solve for $desc...<br>");
      for ($x=1; $x<=9; $x++) {
	determine_sector_loc($desc,$x);
      }
    }
}

function determine_sector_loc($desc,$index1) {
	pdebug("[[ Entering DETERMINE SECTOR_LOC ]]<br>");
	SWITCH($desc){
	    CASE row:
	    	SWITCH($index1){
		    CASE 1:
		    CASE 2:
		    CASE 3:
		      $sector_row=1;
		    break;

		    CASE 4:
		    CASE 5:
		    CASE 6:
		      $sector_row=2;
		    break;

		    CASE 7:
		    CASE 8:
		    CASE 9:
		      $sector_row=3;
		    break;
		}
	    break;

	    CASE column:
	    	SWITCH($index1){
		    CASE 1:
		    CASE 2:
		    CASE 3:
		      $sector_column=1;
		    break;

		    CASE 4:
		    CASE 5:
		    CASE 6:
		      $sector_column=2;
		    break;

		    CASE 7:
		    CASE 8:
		    CASE 9:
		      $sector_column=3;
		    break;
		}
	    break;
	}
	/* Iterate through the cells in this row or column and
	determine if a specific digit is possible in only one
	row or column of the sector.  The sector being defined by
	combining the sector_row and sector_column values much as
	the cell id is a combination of row and column values. */

	pdebug("desc: ".$desc." Sector row: ".$sector_row."
	sector column: ".$sector_column."<br>");

	for ($index2=1; $index2<=9; $index2++){
	    SWITCH($desc){
		CASE row:
		/* Row being represented by index1 and column being
		represented by index2 */
		$id=$index1.$index2;
		$spec="id=".$index1.$index2;
		SWITCH($index2){
		    CASE 1:
		    CASE 2:
		    CASE 3:
		      $sector_column=1;
		    break;

		    CASE 4:
		    CASE 5:
		    CASE 6:
		      $sector_column=2;
		    break;

		    CASE 7:
		    CASE 8:
		    CASE 9:
		      $sector_column=3;
		    break;
		}
		break;

		CASE column:
		$id=$index2.$index1;
		$spec="id=".$index2.$index1;
		SWITCH($index2){
		    CASE 1:
		    CASE 2:
		    CASE 3:
		      $sector_row=1;
		    break;

		    CASE 4:
		    CASE 5:
		    CASE 6:
		      $sector_row=2;
		    break;

		    CASE 7:
		    CASE 8:
		    CASE 9:
		      $sector_row=3;
		    break;
		}
		break;
	    }
	    $sector_loc=$sector_row.$sector_column;

	    mysql_query("UPDATE `cell`
	    SET sector_loc=$sector_loc
	    WHERE $spec")
	    or die(mysql_error());
	}
	pdebug("[[ Leaving DETERMINE SECTOR_LOC ]]<br>");
}

function prompt(){
	/* A way to pause the flow and have the user 
	press enter to continue. */
	echo "<form method='post' action=$PHP_SELF>";
	echo "Press enter to continue <input type='submit' name='enter' value='Enter'><br>";

}

?>
