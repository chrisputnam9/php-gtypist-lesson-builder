<?php
/**
 * php-gtypist-lesson-builder
 */
class Php_Gtypist_Lesson_Builder extends Console_Abstract
{
    const VERSION = "1.0.1";

    // Name of script and directory to store config
    const SHORTNAME = 'php-gtypist-lesson-builder';

    /**
     * Callable Methods
     */
    protected static $METHODS = [
        'create',
    ];

    // Update URL
	// public $update_version_url = "https://raw.githubusercontent.com/chrisputnam9/php-gtypist-lesson-builder/master/readme.md";

    protected $___create = [
        "Create a GNU Typist lesson from a text file",
        ["Path to text file to use as input", "string"],
        ["Where to output gtypist file - defaults to name and location based on input file", "string"],
    ];
	public function create($input, $output=null)
    {
        if (!is_file($input)) {
            $this->error("Input file does not exist ($input)");
        }

		$this->output("Ready to process $input");
    }
}

if (empty($__no_direct_run__))
{
    // Kick it all off
    Php_Gtypist_Lesson_Builder::run($argv);
}

// phpcs:disable PSR2.Files.ClosingTag
// Note: we want this for our simplistic packaging approach ?>
