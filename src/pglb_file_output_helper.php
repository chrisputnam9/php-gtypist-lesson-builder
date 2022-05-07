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

	public function file_output($data) {
		$handle = $this->get_handle();
		$data = $this->pglb->stringify($data);
		fwrite($handle, $data);
	}

	public function file_output_comment($data)
	{
		$data = $this->pglb->stringify($data);
		$this->file_output('# ' . $data . "\n");
	}

	public function file_output_break()
	{
		$this->file_output("\n");
	}

	public function file_output_line($char=' ', $command='')
	{
		$this->file_output($char . ':' . $command . "\n");
	}

	public function file_output_comment_line($char='-', $prefix='')
	{
		$string = str_pad($prefix, 78, $char);
		$this->file_output_comment($string);
	}

	public function file_output_comment_header($data) {
		$this->file_output_comment_line();
		$this->file_output_comment($data);
		$this->file_output_comment_line();
	}
}
