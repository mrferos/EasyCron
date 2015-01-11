<?php
namespace EasyCron\Console\Command;

use EasyCron\Cron\Writer;
use EasyCron\Cron\WriterInterface;
use EasyCron\ECLang\Lexer;
use EasyCron\ECLang\Parser;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class EditCommand extends AbstractCommand
{
    protected $_settings;
    /**
     * @var InputInterface
     */
    protected $_input;
    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * @var WriterInterface
     */
    protected $_cronWriter;

    protected function configure()
    {
        $this->setName('edit')
            ->setDescription("Edits your easycron file")
            ->addOption('easy-file', 'e', InputOption::VALUE_OPTIONAL, 'The easy cron file to edit')
            ->addOption('out-file', 'o', InputOption::VALUE_OPTIONAL, 'The file to output too. Default is /etc/cron.d/{user-name}');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;

        $outputContent = $this->_getEditedFileContent();
        if (empty($outputContent)) {
            $output->writeln('<error>No content in easy file</error>');
            exit (1);
        }

        $lines = explode("\n", $outputContent);
        $cronLines = array();
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            $lexer = new Lexer();
            $lexer->setInput($line);
            $parser = new Parser($lexer);
            $cronLines[] =  $parser->parse();
        }

        $inputFile = $this->_getEasyFile();
        $outputFile = $this->_getOutFile();
        $writer = $this->getCronWriter();
        $writer->setCronjobFile($outputFile);
        $writer->write($cronLines);
        $output->writeln('<info>' . $inputFile . ' has been written to ' . $outputFile);
    }

    /**
     * @return WriterInterface
     */
    public function getCronWriter()
    {
        if (empty($this->_cronWriter)) {
            $this->setCronWriter(new Writer());
        }

        return $this->_cronWriter;
    }

    /**
     * @param WriterInterface $cronWriter
     */
    public function setCronWriter(WriterInterface $cronWriter)
    {
        $this->_cronWriter = $cronWriter;
    }



    protected function _getEasyFile()
    {
        $easyFile = $this->_input->getOption('easy-file');
        if (empty($easyFile)) {
            $easyFile = $this->getFileSystem()->getStorageDir() . '/.easy-cron';
        }

        return $easyFile;
    }

    protected function _getOutFile()
    {
        $outfile = $this->_input->getOption('out-file');
        if (empty($outfile)) {
            $outfile = '/etc/cron.d/' . get_current_user() . '-e';
        }

        return $outfile;
    }

    protected function _getEditedFileContent()
    {
        $this->_openEditor();
        $easyFile = $this->_getEasyFile();
        if (!file_exists($easyFile)) {
            return null;
        }

        return file_get_contents(trim($easyFile));
    }

    protected function _openEditor()
    {
        $settings = $this->getSettings();
        $preferredEditor = $settings->getPreferredEditor();
        if (empty($preferredEditor))
        {
            $availableEditors = $this->_assessAvailableEditors();
            $choiceQuestion = new ChoiceQuestion('Which editor would you like to use?', $availableEditors);
            $question = new QuestionHelper();
            $choice = $question->ask($this->_input, $this->_output, $choiceQuestion);
            $preferredEditor = $choice;
            $settings->setPreferredEditor($preferredEditor);
            $settings->write();
        }

        $easyFile = $this->_getEasyFile();
        exec('/usr/bin/env '. $preferredEditor .' ' . $easyFile . ' > `tty`', $output);
    }

    protected function _assessAvailableEditors()
    {
        $available = array();
        // Check if vim is installed
        exec('vim --version', $vOutput);
        if (!empty($vOutput) && preg_match('/^VIM/', trim($vOutput[0]))) {
            $available[] = 'vim';
        }

        exec('nano --version', $nOutput);
        if (!empty($nOutput) && preg_match('/^GNU nano/', trim($nOutput[0]))) {
            $available[] = 'nano';
        }

        return $available;
    }
}