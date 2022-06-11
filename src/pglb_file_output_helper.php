<?php
/**
 * Output Helpers
 *  - Reference https://www.gnu.org/software/gtypist/doc/#Script-file-commands
 */
class PGLB_File_Output_Helper
{

	protected $pglb = null;

	public function __construct($pglb) {
		$this->pglb = $pglb;
	}

	private $handle = null;
	private function get_handle() {
		if (is_null($this->handle)) {
			$this->handle = $this->pglb->get_output_handle();
			if (is_null($this->handle)) {
				throw new Exception("Missing output_handle - something happened in the wrong order");
			}
		}
		return $this->handle;
	}

	/**
	 * Generic output data to file
	 */
	public function file_output($data) {
		$handle = $this->get_handle();
		$data = $this->pglb->stringify($data);
		fwrite($handle, $data);
	}

	/**
	 * Generic output line - command character and a command or line contents
	 */
	public function file_output_line($char=' ', $command='')
	{
		$this->file_output($char . ':' . $command . "\n");
	}

	/**
	 * General helpers
	 */
	public function file_output_break()
	{
		$this->file_output("\n");
	}

	/**
	 * Comment helpers
	 */
	public function file_output_comment($data)
	{
		$data = $this->pglb->stringify($data);
		$this->file_output('# ' . $data . "\n");
	}

	public function file_output_comment_header($data) {
		$this->file_output_comment_line();
		$this->file_output_comment($data);
		$this->file_output_comment_line();
	}

	public function file_output_comment_line($char='-', $prefix='')
	{
		$string = str_pad($prefix, 78, $char);
		$this->file_output_comment($string);
	}

	/**
	 * Header & Instruction Helpers
	 */
	public function file_output_header($content) {
		$this->file_output_line('B', $content);
	}

	public function file_output_instruction($content) {
		// TODO - this could support multi-line
		$this->file_output_line('I', $content);
	}

	/**
	 * Lesson / Typing Helpers
	 */

	/**
	 * Menu / Label Helpers
	 */
	public function file_output_menu($menu_items, $instructions="Select section") {

	}

	public function file_output_menu_label($menu_id) {
		// TODO could have this check for duplicates
		$this->file_output_line('*', $menu_id);
	}

	public function file_output_menu_goto ($menu_id) {
		// TODO could have this check against labels to validate in some kind of 'finalize' method
		$this->file_output_line('G', $menu_id);
	}

	/**
	 * Lines to be actually typed
	 * @param $lines
	 */
	public function file_output_typing_lines($lines=[]) {
	}

}
