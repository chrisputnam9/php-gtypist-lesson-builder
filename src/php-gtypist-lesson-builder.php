<?php
/**
 * php-gtypist-lesson-builder
 */
class Php_Gtypist_Lesson_Builder extends Console_Abstract
{
    const VERSION = "1.0.1";

    // Name of script and directory to store config
    const SHORTNAME = 'php-gtypist-lesson-builder';

	// Max lengths for typing
	const MAX_CHARS_PER_LINE = 100;
	const MAX_CHARS_PER_SECTION = 600;

	// Patterns
	const PATTERN_LOGICAL_BREAKS = [
		'[.!?]',
		'[\'":;,-)\]]}',
		'\s',
	];

    /**
     * Callable Methods
     */
    protected static $METHODS = [
        'create',
    ];

    // Update URL
	// public $update_version_url = "https://raw.githubusercontent.com/chrisputnam9/php-gtypist-lesson-builder/master/readme.md";

	public function __construct() {
		parent::__construct();
		$this->init_file_output_helper();
	}

    protected $___create = [
        "Create a GNU Typist lesson from a text file",
        ["Path to text file to use as input", "string"],
        ["Where to output gtypist file - defaults to name and location based on input file", "string"],
    ];
	public function create($input, $title=null, $output=null)
    {
		$this->init_files($input, $output);

		if(empty($title)) {
			$title = basename($this->input_path);
			$title = preg_replace('/\.[^.]{2,4}$/i', '', $title);
			$title = preg_replace('/[-_]+/', ' ', $title);
			$title = ucwords($title);
		}

		$this->output('Ready to process:');
		$this->output(" - input: $this->input_path");
		$this->output(" - title: $title");
		$this->output(" - output: $this->output_path");

		$this->file_output_comment_header($title);
		$this->file_output_header($title);
		$this->file_output_break();

		$file_contents = fread($this->input_handle, filesize($this->input_path));
		$file_contents = preg_replace('/('.self::PATTERN_LOGICAL_BREAKS[0].')  /', '$1 ', $file_contents); // Give sentence ends consistent spacing
		$all_lines = explode("\n", $file_contents);

		// Group the lines into sections based on MAX_CHARS_PER_SECTION
		$sections = [];

		$current_line = "";

		// Loop until we have processed all lines
		while (!empty($all_lines)) {
			$this->log("\nALL LINES:");
			$this->log($all_lines);

			// Get a new line if needed
			if (empty($current_line)) $current_line = array_shift($all_lines);

			$new_section = [];
			$chars_in_section = 0;

			// Add lines until we have hit the limit of chars per typing section
			while (true) {

				// todo - LATER add limit of lines per section as well?

				// Get a new line if needed
				if (empty($current_line)) {

					// Stop if we're out of lines
					if (empty($all_lines)) break;

					$current_line = array_shift($all_lines);
				}

				// Trim whitespace
				$current_line = trim($current_line);

				// Skip empty lines
				if (empty($current_line)) continue;

				$this->log("\nCURRENT LINE:");
				$this->log($current_line);

				// Check the current line length
				$current_line_length = strlen($current_line);
				$projected_char_length = $chars_in_section + $current_line_length;

				$cutoff_length = false;
				$section_cutoff = false;

				if ( $projected_char_length > self::MAX_CHARS_PER_SECTION ) {
					$this->log("\nOVER CHAR LIMIT FOR SECTION: $projected_char_length projected section chars");
					$cutoff_length = self::MAX_CHARS_PER_SECTION - $chars_in_section;
					$section_cutoff = true;
				} else if ($current_line_length > self::MAX_CHARS_PER_LINE) {
					$this->log("\nOVER CHAR LIMIT FOR LINE: $current_line_length current line chars");
					$cutoff_length = self::MAX_CHARS_PER_LINE;
				}

				if ( $cutoff_length !== false ) {

					$before_max = substr($current_line, 0, $cutoff_length);
					$after_max = substr($current_line, $cutoff_length);

					// @TODO - keep working on function
					$best_cutoff = $this->get_best_line_split($current_line, $cutoff_length, $section_cutoff);

					// If determined best to push line to next section
					if ($best_cutoff > $cutoff_length) {
						break; // done with this section, go to next
					}

					// Otherwise, go ahead and cut off
					// @TODO2

					// Cut up the line
					// - first part goes in section
					// - remainder is now $current_line for next section
					$new_section[] = substr($current_line, 0, $cutoff_length);
					$chars_in_section+= $cutoff_length;
					$current_line = substr($current_line, $cutoff_length+1);

					// Move on to the next section
					break;

				} else {
					// Otherwise, we're good to add the line into our section
					$new_section[] = $current_line;
					$chars_in_section+= $current_line_length;
					$current_line = "";
				}
			}

			die("<pre>".print_r($new_section,true)."</pre>");

			$sections[]= $new_section;
			$new_section = [];
		}

		die("<pre>".print_r($sections,true)."</pre>");

		$number_of_sections = count($sections);
		foreach ($sections as $s => $lines) {

			// Group the section into lines based on MAX_CHARS_PER_LINE
			$next_length = strlen($next);
			$lines = [];
			while ($next_length > self::MAX_CHARS_PER_LINE) {
				$maximum_line = substr($next, 0, self::MAX_CHARS_PER_LINE + 1);

				// Stop at the last full word before the max
				$line = preg_replace('/\s\S*$/', '', $maximum_line);
				$lines[]= $line;

				$next = substr($next, 0, strlen($line));
				$next_length = strlen($next);
			}
			$this->file_output_instruction($title . ' - section 1 of X');
			$this->file_output_typing_lines($lines);

		}
    }

	/**
	 * Figure out the best place to cut a line of text, based on:
	 *  - The maximum length for the line to fit in the current section ($cutoff_length)
	 *  - The known maximum line length (MAX_CHARS_PER_LINE)
	 *
	 * Try to cut off at a logical break - eg. punctuation when possible
	 *
	 * Especially try not to break between sections outside of a logical break (punctuation)
	 *
	 * @return array [$before, $after] text split into two sections
	 */
	private function get_best_line_split($current_line, $cutoff_length, $section_cutoff) {

		$this->log("Determining best cutoff length before $cutoff_length" . ($section_cutoff ? ' - section cutoff' : ' - line cutoff'));

		$section_cutoff_length = $section_cutoff ? $cutoff_length : self::MAX_CHARS_PER_SECTION;

		$best_cutoff = null;
		$best_section_cutoff = null;

		foreach (self::PATTERN_LOGICAL_BREAKS as $pattern) {

			// Find all logical breaks in the line
			$match_found = preg_match_all('/'.$pattern.' /', $current_line, $matches, PREG_OFFSET_CAPTURE);

			if ( ! $match_found) continue;

			foreach ($matches[0] as $match) {
				$match_index = $match[1];

				// Ideally cut off before our requested length
				if ($match_index < $cutoff_length) {
					$best_cutoff = $match_index;
				}

				// Second best cutoff based on section max
				if ($match_index < $section_cutoff_length) {
					$best_section_cutoff = $match_index;
				}
			}

			// Resort to second-best by section max
			if (empty($best_cutoff)) $best_cutoff = $best_section_cutoff;

			print_r($matches);

			// Quit if we find a good cutoff
			if (!empty($best_cutoff)) {
				// Add 1 character for the punctuation
				$best_cutoff++;
				break;
			}
		}

		// @TODO
		// Final option - cut off 3 before exact length, and add on ellipses

		$this->log("Best cutoff found: $best_cutoff");
		die;

		return $best_cutoff;
	}

	// Manage input and output files
	private $input_handle = null;
	private $output_handle = null;
	private $input_path = null;
	private $output_path = null;
	private function init_files($input_path, $output_path=null) {

        if (!is_file($input_path)) {
            $this->error("Input file does not exist ($input_path)");
        }
		$this->input_path = $input_path;
		$this->input_handle = fopen($input_path, 'r');

		if (empty($output_path)) {
			$output_path = preg_replace('/\.[^.]{2,4}$/i', '', $input_path);
			$output_path.= '.typ';
		}
		$this->output_path = $output_path;
		$this->output_handle = fopen($output_path, 'w');
	}
	public function get_output_handle() {
		return $this->output_handle;
	}

	// Output Helper - just to separate out methds
	private $file_output_helper = null;
	private function init_file_output_helper() {
		$this->file_output_helper = new PGLB_File_Output_Helper($this);
	}

	protected function _shutdown($arglist) {

		if (!is_null($this->input_handle)) {
			fclose($this->input_handle);
		}

		if (!is_null($this->output_handle)) {
			fclose($this->output_handle);
		}

	}

	// Run helpers automatically
	public function __call(string $method, array $arguments = []): mixed
	{
		if ( 0 === strpos($method, 'file_output_' ) ) {
			return call_user_func_array([$this->file_output_helper, $method], $arguments);
		}

		throw new Exception("Invalid method '$method'");
	}
}

if (empty($__no_direct_run__))
{
    // Kick it all off
    Php_Gtypist_Lesson_Builder::run($argv);
}

// phpcs:disable PSR2.Files.ClosingTag
// Note: we want this for our simplistic packaging approach ?>
