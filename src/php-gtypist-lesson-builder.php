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
	const MAX_CHARS_PER_LINE = 50;
	const MAX_CHARS_PER_SECTION = 500;

	// Patterns
	const PATTERN_LOGICAL_BREAK = '[.!?"\',]';

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
		$file_contents = preg_replace('/('.self::PATTERN_LOGICAL_BREAK.')  /', '\. ', $file_contents); // Make sentence ends consistent
		$all_lines = explode("\n", $file_contents);

		// Group the lines into sections based on MAX_CHARS_PER_SECTION
		$sections = [];

		$current_line = "";

		while (!empty($all_lines)) {

			// Get a new line if needed
			if (empty($current_line)) $current_line = array_shift($all_lines);

			$new_section = [];
			$chars_in_section = 0;

			while (true) {

				// Get a new line if needed
				if (empty($current_line)) $current_line = array_shift($all_lines);

				// Check the current line length
				$current_line_length = strlen($current_line);

				if ( ($chars_in_section + $current_line_length) > self::MAX_CHARS_PER_SECTION ) {

					$cutoff_length = self::MAX_CHARS_PER_SECTION - $chars_in_section;
					$before_max = substr($current_line, 0, $cutoff_length);
					$after_max = substr($current_line, $cutoff_length);

					// Find the last period before the max
					$line_parts = preg_split('/('.self::PATTERN_LOGICAL_BREAK.') /', $before_max);
					echo("<pre>".print_r($sections,true)."</pre>");
					echo("<pre>".print_r($new_section,true)."</pre>");
					die("<pre>".print_r($line_parts,true)."</pre>");

					// todo

					// Failing that, find the last space before the max
					// todo

					// Failing that, cut off exactly (odd situation)
					// todo

					// Cut up the line
					// - first part goes in section
					// - remainder is now $current_line for next section
					// todo

					// Move on to the next section
					// todo
				}
			}
		}

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
	public function __call($method, $arguments)
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
