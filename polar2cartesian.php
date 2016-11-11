<?php

/* **************************************************************** **

POLAR COORDINATES TO CARTESIAN COORDINATES CONVERSION SCRIPT



Author: CALYO PEREONIS DELPHI (DRAGON-ARCHITECT/DRAGARCH)



Copyright 2016 Calyo Pereonis Delphi (dragon-architect/dragarch)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

** **************************************************************** */



/* **************************************************************** **
PRIMARY FUNCTION: polar2cartesian()

DESCRIPTION:
    This simple function returns an array of cartesian coordinates, converted from polar coordinates.

INPUT PARAMETERS:
    $radius     The radius of the point in a polar coordinate system
    $angle      The angle or azimuth upon which the point lies
                0°/0rad is on the left, and degrees are measured counterclockwise
    $radians    Indicates whether or not radians are in use
                Default value is false

RETURNS:
    An array of cartesian coordinates with the following indices:
        [0]     =>  $x
        [1]     =>  $y
        ['x']   =>  $x
        ['y']   =>  $y

Both coordinates are defined at two pairs of indices to be compatible with different coding styles
** **************************************************************** */
function polar2cartesian($radius, $angle, $radians = false)
{
    // Calculate cartesian coordinates from polar coordinates:
    $x = $radius * cos($radians ? $angle : deg2rad($angle));
    $y = $radius * sin($radians ? $angle : deg2rad($angle));
    // Return as an array
    return array($x, $y, "x" => $x, "y" => $y);
}



/* **************** *************** **************** */
/* **************** BEGIN CLI CLASS **************** */
/* **************** *************** **************** */
class pol2cart
{

// Private variables for the polar and cartesian coordinates
private $radius  = null;
private $angle   = null;
private $radians = null;
private $coords  = null;

// Used for setting output precision
private $precision = 3;

// Used by internal member functions
private $interactive = false;
private $nice        = false;

// This is LITERALLY the ONLY public thing in this entire class
// Its entire job is to call the internal parseOpts() function
function __construct() { $this->parseOpts(); }

/* **************************************************************** **
FUNCTION: pol2cart::parseopts()

DESCRIPTION:
    Parses any input options provided by the user if invoked from command line
    Then does what it needs to do to set properties and call methods

INPUT PARAMETERS:
    NONE

RETURNS:
    NOTHING
** **************************************************************** */
private function parseopts()
{
    // Support for both short and long options :3
    // There's a lot of names for the angle coordinate, so there's a lot of
    // long options that are equivalent to each other.
    $shortopts = "hinr:a:t:cdp:";
    $longopts  = array(
        "help",
        "interactive",
        "nice",
        "radius:", "rho:",
        "angle:", "azimuth:", "phi:", "theta:",
        "radians", "rad",
        "degrees", "deg",
        "precision",
    );
    $options = getopt($shortopts, $longopts);
    
    // Iterate through all of the options in the array
    // And do the necessary stuff
    foreach($options as $option => $value)
    {
        // Let's do some dirty work on the options
        switch($option)
        {
          // Self explanatory lol
          case "h": case "help":
            $this->help();
            break;
          // Invoke the interactive mode
          case "i": case "interactive":
            $this->interactive = true;
            $this->interactiveMode();
            break;
          // Not implemented yet, this will enable reading from a batch file of polar coordinates
          // And output cartesian coordinates to standard output to be redirected as desired
          //case "f": case "file":
          // Toggle nice output for normal CLI invocation
          case "n": case "nice":
            $this->nice = true;
            break;
          // Set the radius polar coordinate
          case "r": case "radius": case "rho":
            $this->radius = floatval($value);
            break;
          // Set the angular polar coordinate
          case "a": case "angle": case "azimuth":
          case "t": case "theta": case "phi":
            // floatval() ignores trailing non-numeric characters
            $this->angle = floatval($value);
            // Checks to see if the angle is post-fixed with r, c, rad, or radians
            // Short-circuits if $radians is already pre-set by:
            // -d, --deg, --degrees, -c, --rad, or --radians
            // coming earlier in the options list
            if(is_null($this->radians)) $this->radians = $this->parseRadiansFromAngle($value);
            break;
          // Force degrees?
          case "d": case "deg": case "degrees":
            $this->radians = false;
            break;
          // Or force radians?
          case "c": case "rad": case "radians":
            $this->radians = true;
            break;
          // Set decimal precision
          case "p": case "precision":
            $this->precision = intval($value);
            break;
        }
    }
    
    // Check to make sure the user has provided both polar coordinates
    // Prompt for the user to provide each in turn if not provided
    if(is_null($this->radius))
        $this->radius = floatval(readline("Please provide Radius (r): "));
    if(is_null($this->angle ))
    {
        $value  = readline("Please provide Angle (θ): ");
        $this->angle  = floatval($value);
        if(is_null($this->radians)) $this->radians = $this->parseRadiansFromAngle($value);
    }
    
    // Call the main function to compute the coordinates
    $this->coords = polar2cartesian($this->radius, $this->angle, $this->radians);
    
    // Output the coordinates~
    $this->clioutput();
}

/* **************************************************************** **
FUNCTION: pol2cart::help()

DESCRIPTION:
    Self explanatory. Prints a help dialog. That's it.

INPUT PARAMETERS:
    NONE

RETURNS:
    NOTHING
** **************************************************************** */
private function help()
{
echo <<<END

NAME
    polar2cartesian.php
    
    Polar Coordinates to Cartesian Coordinates Calculator.

USAGE
    Within another php script:
        require_once('polar2cartesian.php');
        
        \$coords = polar2cartesian(\$radius, \$angle[, \$radians = true]);
        
    From command line:
        php polar2cartesian.php [options]
        
        php polar2cartesian.php -r radius -a angle [[-c] [-d]]

DESCRIPTION
    array polar2cartesian( float \$radius, float \$angle[, bool \$radians] )
    
    polar2cartesian.php is a basic calculator to convert from Polar Coordinates
    (radius, angle) to Cartesian Coordinates (x, y).
    
    This can be used directly in any php script or invoked from command line.
    
    Using parameter -i or --interactive you can enter an interactive mode.
    
    Using parameters -r, --radius, -a, --angle, you can provide the radius and
    angle. Degrees are assumed by default. Use option -c or --radians to set
    angle units to radians.

RETURNS
    An array of cartesian coordinates converted from provided polar coordinates
    with the following indices:
    
    [0]     => \$x
    [1]     => \$y
    ['x']   => \$x
    ['y']   => \$y

OPTIONS
    --help
    -h          Print this help dialog.
    
    --interactive
    -i          Invoke the interactive mode. When defining decimal precision,
                -i MUST be the last option. -p MUST precede -i.
    
    --nice
    -n          Produce nice output. Each x and y coordinate is on its own line.
    
    --file file
    -f file     Specify input file. Not implemented yet.
    
    --radius radius
    --rho radius
    -r radius   Set the radius polar coordinate.
    
    --angle angle
    --azimuth angle
    --phi angle
    --theta angle
    -a angle
    -t angle    Set the anglular polar coordinate, also known as theta (θ) or
                the azimuth. Angle can optionally be post-fixed with any of the
                following to indicate radians: r, c, rad, radian, or radians.
                Otherwise, default angle units are degrees.
    
    --degrees
    --deg
    -d          Force angle units to be degrees. This overrides angle post-fix.
    
    --radians
    --rad
    -c          Force angle units to be radians. This overrides angle post-fix.
    
    --precision places
    -p places   Sets desired decimal places of precision. Default is 3.
                NOTE: If using interactive mode, -p MUST precede -i.


END;

exit; // Exit the script entirely at the end of this
}

/* **************************************************************** **
FUNCTION: pol2cart::parseRadiansFromAngle()

DESCRIPTION:
    Self explanatory. Prints a help dialog. That's it.

INPUT PARAMETERS:
    $angle      A string representing the angle/azimuth of the polar coordinate pair
                This string may be postfixed with r, c, rad, radian, or radians

RETURNS:
    true        If the input parameter is postfixed with r, c, rad, radian, or radians
    false       Otherwise
** **************************************************************** */
private function parseRadiansFromAngle($angle)
{
    // Checks to see if the angle is post-fixed with r, c, rad, or radians
    return (bool) preg_match("/[rc]|rad(ians?)?$/", $angle);
}

/* **************************************************************** **
FUNCTION: pol2cart::getOrderOfMagnitude()

DESCRIPTION:
    This function does some odd stuff, but it's important for CLI output formatting.
    It calculates the input number's order of magnitude on a base-10 logarithmic scale.
    What I mean by this is that log10(x) returns a decimal answer such that:
        The integer/whole part is the "nearest" integer-power-of-ten to x:
            10^n <= x such that n is an integer and n >= 0 (n is zero or positive)
            10^n >= x such that n is an integer and n <= 0 (n is zero or negative)
        The decimal/fractional part is the extra little bit to go the rest of the way:
            10^(n+m) == 10^n * 10^m == x such that n fulfills the above conditions and 0 < m < 1
    Essentially, log10(x) returns the exponent p such that 10^p = x

INPUT PARAMETERS:
    $number     A number, either integer or float

RETURNS:
    The number's order of magnitude + the user's defined decimal precision + 2
** **************************************************************** */
private function getOrderOfMagnitude($number)
{
    /*
    Determines the coordinate's order of magnitude:
    1. abs() first strips the sign by getting absolute value
    2. log10() gets the base-10 logarithm; the whole part is the power-of-10
    3. floor() strips the decimal part to leave a whole integer
    
    Oh, and the truthiness check on $number? Covers the edge case of feeding in a value of exactly 0.
    log(0), logn(0), and log10(0), all return -INF exactly as documented.
    php returns what is the value of log(0) if you take the limit of log(x) as x approaches 0 from the right (positive side).
    It tends towards negative infinity. Yay calculus! \o/
    (This is a better solution than just returning null or undefined, which calculators do.
    Unless you're using a TI-89 or better, or Wolfram Alpha, your calculator cain't do calculus. :v)
    */
    $magnitude = floor(log10(abs($number ?: 1)));
    
    // If $magnitude is not 0, use $magnitude, otherwise use 1
    // + 2 accounts for the sign & decimal point in the output formatting
    return ($magnitude ?: 1) + ($this->precision + 2);
}

/* **************************************************************** **
FUNCTION: pol2cart::clioutput()

DESCRIPTION:
    This function produces command line output. It calls pol2cart::getOrderOfMagnitude() to produce the necessary sprintf() formatting strings.
    When formatting floats using printf syntax, it's usually in the form of {x}.{p}f such that:
        {x} is the total width of the output cell in characters
        {p} is the desired decimal precision of the floating point number
    So to make sure sprintf() prints the full number, I use a separate function to get the order of magnitude of that number
    gOOM returns a number that is effectively:
        The order of magnitude (how many digits in the whole part)
        Plus the decimal precision
        Plus 2 to account for the sign and decimal point
    And that number is used in here when setting output formats.
    There's also three output format modes: interactive, nice, and default:
        Interactive mode outputs the cartesian coordinats in (x,y) format.
        Nice mode outputs the cartesian coordinates one per line. It's meant to make it easier to read when just using directly on command line.
        Default mode just puts a tab between the coordinates and a newline at the end so they can be redirected as standard output to other things like output files.
            Default mode will also be the default output format for processing batch files of coordinates as well

INPUT PARAMETERS:
    NONE

RETURNS:
    NONE
** **************************************************************** */
private function clioutput()
{
    // Get the ordrs of magnitude of the x and y coordinates,
    // adjusted to account for the decimal & 3 places of precision
    $xformat = "%1$".$this->getOrderOfMagnitude($this->coords['x']).".{$this->precision}f";
    $yformat = "%2$".$this->getOrderOfMagnitude($this->coords['y']).".{$this->precision}f";
    
    // Set the output format
    // I used to use ternary operators but it became unwieldy as soon as I had to separate the output formats for both interactive and nice modes
    // So for the sake of maintainability, if/elseif/else! :3
         if($this->interactive) $format = "Cartesian Coordinates (x,y): ({$xformat},{$yformat})\n\n";
    else if($this->nice       ) $format = "X Coord = $xformat\nY Coord = $yformat\n";
    else                        $format = "$xformat\t$yformat\n";
    
    echo sprintf($format, $this->coords['x'], $this->coords['y']);
}

/* **************************************************************** **
FUNCTION: pol2cart::interactiveMode()

DESCRIPTION:
    This function is the interactive mode, invoked by:
        php polar2cartesian.php [[-pprecision] [--precision=precision]] -i
        php polar2cartesian.php [[-pprecision] [--precision=precision]] --interactive
    The user provides polar coordinates as comma-separated values, optionally with or without (parentheses) and whitespace
    Then interactive mode spits out the cartesian coordinates, and prompts again for another pair of polar coordinates
    Type 'exit' or 'quit' at any time to exit interactive mode and terminate the script

INPUT PARAMETERS:
    NONE

RETURNS:
    NONE
** **************************************************************** */
private function interactiveMode()
{
// Output interactive mode greeting and instructions
echo <<<END

Polar to Cartesian coordinates converter interactive mode!
Type 'exit' or 'quit' at any time to exit interactive mode.
Angle default units are degrees. Post-fix angle with either r, c, rad, radian,
    or radians to set angle measurement to radians.

Remeber:
    0°/0rad (zero degrees/radians) is on the right.
    Degrees/radians are measured counter-clockwise.


END;
    
    // INFINITE LOOP OH NO!! /)OoO(\
    while(true)
    {
        // Prompt for input
        // Old version of this prompted twice, separately, for each coordinate
        list($this->radius, $this->angle) = explode(",", trim(readline("Polar coordinates (r,θ): "), " \t\n\r\0\x0B()" ) );
        
        // Check if either $radius or $angle holds the values "exit" or "quit"
        foreach(array($this->radius, $this->angle) as $value)
        {
            switch($value)
            {
              case "exit":
              case "quit":
                echo PHP_EOL;
                exit; // Don't worry, this breaks the loop ^_^
              // Default behavior is to keep on truckin'
              default: break;
            }
        }
        
        // Checks to see if the angle is post-fixed with r, c, rad, or radians
        $this->radians = $this->parseRadiansFromAngle($this->angle);
        
        // Get the coordinates. floatval() guarantees input is number type
        // floatval() also ignores trailing non-number characters
        // So there's no need to sanitize the angle
        $this->coords = polar2cartesian(floatval($this->radius), floatval($this->angle), $this->radians);
        
        $this->clioutput();
    }
}

}
/* **************** ************* **************** */
/* **************** END CLI CLASS **************** */
/* **************** ************* **************** */



// If invoked from command line with options, parse command line options. Otherwise, do nothing.
if(preg_match("/cli/", php_sapi_name()) and (count($_SERVER["argv"]) > 1)) $polar2cartesianCLI = new pol2cart();

?>
