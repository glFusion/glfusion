<?php
/**
 * Base class to handle poll questions.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020 Lee Garner <lee@leegarner.com>
 * @package     polls
 * @version     v3.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Polls;


/**
 * Base class for poll questions.
 * @package polls
 */
class Question
{
    /** Question record ID.
     * @var integer */
    private $qid = -1;

    /** Related poll's record ID.
     * @var string */
    private $pid = '';

    /** Question text.
     * @var string */
    private $question = '';

    /** Flage to delete the question.
     * Used if the poll is edited and a question is removed.
     * @var boolean */
    private $deleteFlag = 0;

    /** HTML filter.
     * @var object */
    private $filterS = NULL;

    /** Array of answer objects.
     * @var array */
    private $Answers = array();


    /**
     * Constructor.
     *
     * @param   array   $A      Optional data record
     */
    public function __construct($A=NULL)
    {
        global $_USER, $_TABLES;

        if (is_array($A)) {
            $this->setVars($A, true);
        }
        if ($this->qid > -1 && !empty($this->pid)) {
            $this->Answers = Answer::getByQuestion($this->qid, $this->pid);
        }
    }


    /**
     * Read this field definition from the database and load the object.
     *
     * @see     self::setVars()
     * @param   integer $id     Record ID of question
     * @return  array           DB record array
     */
    public static function Read($id = 0)
    {
        global $_TABLES;
        $id = (int)$id;
        $sql = "SELECT * FROM {$_TABLES['pollquestions']}
            WHERE qid = $id";
        $res = DB_query($sql, 1);
        if (DB_error() || !$res) return false;
        $A = DB_fetchArray($res, false);
        return $A;
    }


    /**
     * Get all the questions that appear on a given poll.
     *
     * @param   string  $pid    Poll ID
     * @return  array       Array of Question objects
     */
    public static function getByPoll($pid)
    {
        global $_TABLES;

        $retval = array();
        $sql = "SELECT * FROM {$_TABLES['pollquestions']}
            WHERE pid = '" . DB_escapeString($pid) . "'
            ORDER BY pid,qid ASC";
        $res = DB_query($sql, 1);
        while ($A = DB_fetchArray($res, false)) {
            $retval[] = new self($A);
        }
        return $retval;
     }


    /**
     * Set all variables for this field.
     * Data is expected to be from $_POST or a database record
     *
     * @param   array   $A      Array of name->value pairs
     * @param   boolean $fromDB Indicate whether this is read from the DB
     */
    public function setVars($A, $fromDB=false)
    {
        if (!is_array($A)) {
            return false;
        }
        $this->qid = (int)$A['qid'];
        $this->pid = COM_sanitizeID($A['pid']);
        $this->question = $A['question'];
        return $this;
    }


    /**
     * Set the poll ID. Used when creating a new question.
     *
     * @param   string  $pid    Poll ID
     * @return  object  $this
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
        return $this;
    }


    /**
     * Set the question text. Used when creating a new question.
     *
     * @param   string  $q      Question text
     * @return  object  $this
     */
    public function setQuestion($q)
    {
        $this->question = $q;
        return $this;
    }


    /**
     * Set the answers for this question.
     *
     * @param   array   $A      Array of anwer strings
     * @return  object  $this
     */
    public function setAnswers($A)
    {
        global $_PO_CONF;

        for ($i = 0; $i < $_PO_CONF['maxanswers']; $i++) {
            if ($A['answer'][$this->qid][$i] == '') break;
            if (!isset($this->Answers[$i])) {
                COM_errorLog("Answer now found, creating new answer $i for question {$this->qid}");
                $this->Answers[$i] = new Answer;
                $this->Answers[$i]->setQid($this->qid)->setPid($this->pid);
            }
            $this->Answers[$i]->setAnswer($A['answer'][$this->qid][$i])
                ->setAid($i)
                ->setVotes($A['votes'][$this->qid][$i])
                ->setRemark($A['remark'][$this->qid][$i])
                //;
                ->Save();
        }
        for (; $i < count($this->Answers); $i++) {
            $this->Answers[$i]->Delete();
            unset($this->Answers[$i]);
        }
        return $this;
    }


    /**
     * Render the question.
     *
     * @param   integer $q_num  Sequential question number, e.g. first=1, etc.
     * @param   integer $num_q  Total number of questions for this quiz
     * @return  string  HTML for the question form
     */
    public function Render($cnt, $aid)
    {
        global $_CONF;

        $T = new \Template($_CONF['path'] . 'plugins/polls/templates/admin/');
        $T->set_file(array(
            'question' => 'pollquestions.thtml',
        ) );
        $T->set_var('poll_question', $this->getQuestion());
        $T->set_var('question_id', $cnt);
        $notification = "";
        $Answers = $this->getAnswers();
        $nanswers = count($Answers);
        for ($i=0; $i < $nanswers; $i++) {
            $Answer = $Answers[$j];
            if (($j < count($aid)) && ($aid[$j] == $Answer->getAid())) {
                $poll->set_var('selected', ' checked="checked"');
            }

            $T->set_var(array(
                'answer_id' => $Answer->getAid(),
                'answer_text' => $this->filterS->filterData($Answer->getAnswer()),
            ) );
            $T->set_var('poll_answers', 'panswer',true);
            $T->clear_var('selected');
            $poll->parse('poll_questions', 'pquestions', true);
            $poll->clear_var('poll_answers');

            $T->parse('Answer', 'AnswerRow', true);
        }
        $T->parse('output', 'question');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Create the input selection for one answer.
     * Does not display the text for the answer, only the input element.
     * Must be overridden by the actual question class (radio, etc.)
     *
     * @param   integer $a_id   Answer ID
     * @return  string          HTML for input element
     */
    protected function makeSelection($a_id)
    {
        return '';
    }


    /**
     * Check whether the supplied answer ID is correct for this question.
     *
     * @param   integer $a_id   Answer ID
     * @return  float       Percentage of options correct.
     */
    public function Verify($a_id)
    {
        return (float)0;
    }


    /**
     * Get the ID of the correct answer.
     * Returns an array regardless of the actuall numbrer of possibilities
     * to ensure uniform handling by the caller.
     *
     * @return   array      Array of correct answer IDs
     */
    public function getCorrectAnswers()
    {
        return array();
    }


    /**
     * Edit a question definition.
     *
     * @return  string      HTML for editing form
     */
    public function EditDef()
    {
        global $_TABLES;

        $retval = '';
        $format_str = '';
        $listinput = '';

        // Get defaults from the form, if defined
        /*if ($this->quizID > 0) {
            $form = Quiz::getInstance($this->quizID);
        }*/
        $T = new \Template(QUIZ_PI_PATH. '/templates/admin');
        $T->set_file('editform', 'editquestion.thtml');
        $T->set_var(array(
            'quiz_name' => DB_getItem($_TABLES['quizzer_quizzes'], 'quizName',
                            "quizID='" . DB_escapeString($this->quizID) . "'"),
            'quizID'   => $this->quizID,
            'questionID'    => $this->questionID,
            'question'      => $this->questionText,
            'ena_chk'   => $this->enabled == 1 ? 'checked="checked"' : '',
            'doc_url'   => QUIZ_getDocURL('question_def.html'),
            'editing'   => $this->isNew() ? '' : 'true',
            'help_msg'  => $this->help_msg,
            'postAnswerMsg' => $this->postAnswerMsg,
            'can_delete' => $this->isNew() || $this->_wasAnswered() ? false : true,
            $this->questionType . '_sel' => 'selected="selected"',
            'pcred_vis' => $this->allowPartial() ? '' : 'none',
            'random_chk' => $this->randomizeAnswers ? 'checked="checked"' : '',
            'pcred_chk' => $this->isPartialAllowed() ? 'checked="checked"' : '',
        ) );

        $T->set_block('editform', 'Answers', 'Ans');
        foreach ($this->Answers as $answer) {
            $T->set_var(array(
                'ans_id'    => $answer->getAid(),
                'ans_val'   => $answer->getValue(),
                'ischecked' => $answer->isCorrect() ? 'checked="checked"' : '',
                'isRadio'   => $this->questionType == 'radio' ? true : false,
            ) );
            $T->parse('Ans', 'Answers', true);
        }
        $count = count($this->Answers);
        for ($i = $count + 1; $i <= self::MAX_ANSWERS; $i++) {
            $T->set_var(array(
                'ans_id'    => $i,
                'ans_val'   => '',
                'isRadio'   => $this->questionType == 'radio' ? true : false,
                'ischecked' => '',
            ) );
            $T->parse('Ans', 'Answers', true);
        }
        $T->parse('output', 'editform');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Save the question definition to the database.
     *
     * @param   array   $A  Array of name->value pairs
     * @return  string          Error message, or empty string for success
     */
    public function Save()
    {
        global $_TABLES;

        $sql = "INSERT INTO {$_TABLES['pollquestions']} SET 
            pid = '" . DB_escapeString($this->pid) . "',
            qid = {$this->getQid()},
            question = '" . DB_escapeString($this->question) . "'
            ON DUPLICATE KEY UPDATE
            question = '" . DB_escapeString($this->question) . "'";
        DB_query($sql, 1);
        if (DB_error()) {
            return 5;
        }
        return 0;
    }


    /**
     * Delete the current question definition.
     */
    public function Delete()
    {
        global $_TABLES;

        DB_delete($_TABLES['pollquestions'], 'qid', $this->qid);
        DB_delete($_TABLES['pollanswers'], 'qid', $this->qid);
    }


    /**
     * Save a submitted answer to the database.
     *
     * @param   mixed   $value  Data value to save
     * @param   integer $res_id Result ID associated with this field
     * @return  boolean     True on success, False on failure
     */
    public function SaveData($value, $res_id)
    {
        global $_TABLES;

        $res_id = (int)$res_id;
        if ($res_id == 0)
            return false;

        return Value::Save($res_id, $this->questionID, $value);
    }


    /**
     * Copy this question to another quiz.
     *
     * @see     Quiz::Duplicate()
     */
    public function Duplicate()
    {
        global $_TABLES;

        $sql .= "INSERT INTO {$_TABLES['quizzer_questions']} SET
                quizID = '" . DB_escapeString($this->quizID) . "',
                type = '" . DB_escapeString($this->questionType) . "',
                enabled = {$this->enabled},
                help_msg = '" . DB_escapeString($this->help_msg) . "'";
        DB_query($sql, 1);
        $msg = DB_error() ? 5 : '';
        return $msg;
    }


    /**
     * Toggle a boolean field in the database.
     *
     * @param   integer $id     Question def ID
     * @param   string  $fld    DB field name to change
     * @param   integer $oldval Original value
     * @return  integer         New value
     */
    public static function toggle($id, $fld, $oldval)
    {
        global $_TABLES;

        $id = DB_escapeString($id);
        $fld = DB_escapeString($fld);
        $oldval = $oldval == 0 ? 0 : 1;
        $newval = $oldval == 0 ? 1 : 0;
        $sql = "UPDATE {$_TABLES['quizzer_questions']}
                SET $fld = $newval
                WHERE questionID = '$id'";
        $res = DB_query($sql, 1);
        if (DB_error($res)) {
            COM_errorLog(__CLASS__ . '\\' . __FUNCTION__ . ':: ' . $sql);
            return $oldval;
        } else {
            return $newval;
        }
    }


    /**
     * Get all the questions to show for a quiz.
     * This returns an array of question objects for a new quiz submission.
     *
     * @param   integer $quizID    Quiz ID
     * @param   integer $max        Max questions, default to all
     * @param   boolean $rand       True to randomizeAnswers the return array
     * @return  array       Array of question data
     */
    public static function getQuestions($quizID, $max = 0, $rand = true)
    {
        global $_TABLES;

        $max = (int)$max;
        $sql = "SELECT * FROM {$_TABLES['quizzer_questions']}
                WHERE quizID = '" . DB_escapeString($quizID) . "'
                AND enabled = 1";
        if ($rand) $sql .= ' ORDER BY RAND()';
        if ($max > 0) $sql .= " LIMIT $max";
        //echo $sql;die;
        $res = DB_query($sql);

        // Question #0 indicates the start of the quiz, so index actual
        // questions starting at #1
        $questions = array();   // array of question objects to return
        $i = 0;
        while ($A = DB_fetchArray($res, false)) {
            $questions[++$i] = self::getInstance($A);
        }
        return $questions;
    }


    /**
     * Get all the questions for a result set.
     *
     * @param   array   $ids    Array of question ids, from the resultset
     * @return  array       Array of question objects
     */
    public static function getByIds($ids)
    {
        global $_TABLES;

        $questions = array();
        foreach ($ids as $id) {
            $questons[] = new self($id);
        }
        return $questions;
    }


    /**
     * Determine whether this question was ever answered.
     * Used to see if the question may be deleted without affecting existing
     * results.
     *
     * @return  boolean     True if there is an answer, False if not
     */
    private function _wasAnswered()
    {
        global $_TABLES;

        if (DB_count(
            $_TABLES['quizzer_values'],
            'questionID',
            $this->questionID
        ) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get a count of questions created for a quiz.
     * Used to determine the number of questions to ask, if this number
     * is less than the number assigned to the quiz.
     *
     * @param   string  $quizID    ID of the quiz
     * return   integer     Number of quiz questions in the database
     */
    public static function countQ($quizID)
    {
        global $_TABLES;

        return DB_count($_TABLES['quizzer_questions'], 'quizID', $quizID);
    }


    /**
     * Check if this question type allows partial credit.
     * Used to determine whether the partial credit option is shown on the
     * question definition form.
     *
     * @return  boolean     True if partial credit is allowed
     */
    protected function allowPartial()
    {
        return false;
    }


    /**
     * Check if this question allows partial credit.
     * Only returns true if the question type and question itself
     * allow partial credit.
     *
     * @return  boolean     True if partial credit allowed, False if not
     */
    protected function allowsPartialCredit()
    {
        return $this->allowPartial() && $this->allowPartialCredit;
    }


    /**
     * Check if partial credit is allowed for this question.
     * Must have the partial checkbox checked, and be allowed by
     * the question type.
     *
     * @return  boolean     True if partial credit allowed
     */
    public function isPartialAllowed()
    {
        return $this->allowPartial() && $this->allowPartialCredit;
    }


    /**
     * Uses lib-admin to list the question definitions and allow updating.
     *
     * @param   string  $quizID    ID of quiz
     * @return  string              HTML for the question list
     */
    public static function adminList($quizID = '')
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_QUIZ, $_PO_CONF;

        // Import administration functions
        USES_lib_admin();

        $header_arr = array(
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => $LANG_QUIZ['question'],
                'field' => 'questionText',
                'sort'  => false,
            ),
            array(
                'text'  => $LANG_QUIZ['type'],
                'field' => 'questionType',
                'sort'  => false,
            ),
            array(
                'text'  => $LANG_QUIZ['enabled'],
                'field' => 'enabled',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center',
            ),
        );

        $defsort_arr = array(
            'field'     => 'questionID',
            'direction' => 'ASC',
        );
        $text_arr = array(
            'form_url' => QUIZ_ADMIN_URL . '/index.php',
        );
        $options_arr = array(
            'chkdelete' => true,
            'chkname'   => 'delquestion',
            'chkfield'  => 'questionID',
        );
        $query_arr = array(
            'table' => 'quizzer_questions',
            'sql'   => "SELECT * FROM {$_TABLES['quizzer_questions']}",
            'query_fields' => array('name', 'type', 'value'),
            'default_filter' => '',
        );
        if ($quizID != '') {
            $query_arr['sql'] .= " WHERE quizID='" . DB_escapeString($quizID) . "'";
        }
        $form_arr = array();
        $T = new \Template(QUIZ_PI_PATH . '/templates/admin');
        $T->set_file('questions', 'questions.thtml');
        $T->set_var(array(
            'action_url'    => QUIZ_ADMIN_URL . '/index.php',
            'quizID'       => $quizID,
            'pi_url'        => QUIZ_PI_URL,
            'question_adminlist' => ADMIN_list(
                'quizzer_questions',
                array(__CLASS__, 'getAdminField'),
                $header_arr,
                $text_arr, $query_arr, $defsort_arr, '', '',
                $options_arr, $form_arr
            ),
        ) );
        $T->parse('output', 'questions');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Determine what to display in the admin list for each field.
     *
     * @param   string  $fieldname  Name of the field, from database
     * @param   mixed   $fieldvalue Value of the current field
     * @param   array   $A          Array of all name/field pairs
     * @param   array   $icon_arr   Array of system icons
     * @return  string              HTML for the field cell
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $_PO_CONF, $LANG_ACCESS, $LANG_QUIZ;

        $retval = '';

        switch($fieldname) {
        case 'edit':
            $retval = COM_createLink(
                Icon::getHTML('edit'),
                QUIZ_ADMIN_URL . "/index.php?editquestion=x&amp;questionID={$A['questionID']}"
            );
            break;

        case 'delete':
            $retval = COM_createLink(
                Icon::getHTML('delete'),
                QUIZ_ADMIN_URL . '/index.php?delQuestion=x&questionID=' .
                    $A['questionID'] . '&quizID=' . $A['quizID'],
                array(
                    'onclick' => "return confirm('{$LANG_QUIZ['confirm_delete']}');",
                )
            );
           break;

        case 'enabled':
            if ($A[$fieldname] == 1) {
                $chk = ' checked ';
                $enabled = 1;
            } else {
                $chk = '';
                $enabled = 0;
            }
            $retval = "<input name=\"{$fieldname}_{$A['questionID']}\" " .
                "type=\"checkbox\" $chk " .
                "onclick='QUIZtoggleEnabled(this, \"{$A['questionID']}\", \"question\", \"{$fieldname}\", \"" . QUIZ_ADMIN_URL . "\");' ".
                "/>\n";
            break;

        case 'id':
        case 'questionID':
            return '';
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
        return $retval;
    }


    /**
     * Check if this is a new record or an existing one.
     *
     * @return  integer     1 if new, 0 if existing
     */
    public function isNew()
    {
        return $this->qid == 0 ? 1 : 0;
    }


    public function setQid($qid)
    {
        $this->qid = (int)$qid;
        return $this;
    }


    /**
     * Get the record ID for this question.
     *
     * @return  integer     Record ID
     */
    public function getQid()
    {
        return (int)$this->qid;
    }


    /**
     * Get the text for this question.
     *
     * @return  string      Question text to display
     */
    public function getQuestion()
    {
        return $this->question;
    }


    /**
     * Get the possible answers for this question.
     *
     * @return  array       Array of answer records
     */
    public function getAnswers()
    {
        return $this->Answers;
    }


    /**
     * Get the message to display post-answer.
     *
     * @return  string      Answer message
     */
    public function getAnswerMsg()
    {
        return $this->postAnswerMsg;
    }


    /**
     * Get the sequence number for this question's appearance.
     *
     * @param   integer $seq    Sequence number
     * @return  object  $this
     */
    public function setSeq($seq)
    {
        $this->_seq = $seq;
        return $this;
    }


    /**
     * Get the sequence number for this question's appearance.
     *
     * @return  integer     Sequence number
     */
    public function getSeq()
    {
        return (int)$this->_seq;
    }


    /**
     * Set the total number of questions being asked.
     * Used to create the progress bar.
     *
     * @param   intger  $num    Number of questions on the quiz
     * @return  object  $this
     */
    public function setTotalQ($num)
    {
        $this->_totalAsked = (int)$num;
        return $this;
    }

}

?>
