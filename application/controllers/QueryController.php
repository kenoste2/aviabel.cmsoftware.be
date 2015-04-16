<?php

require_once 'application/controllers/BaseController.php';

class QueryController extends BaseController
{
    public function executeAction() {
        if($this->auth->online_user === 'ADMIN') {
            //WARNING: only the admin user should ever have access to this page.
            $form = new Application_Form_Query();
            $this->view->form = $form;

            if ($this->getRequest()->isPost()) {
                if($form->isValid($this->getRequest()->getPost())) {

                    $now = new DateTime();
                    if(trim($form->getValue('VERIFICATION')) === $now->format('YmdH'))
                    {
                        $this->executeAndRenderQueries($form);
                    }
                }
            }
        } else {
            die('');
        }
    }

    /**
     * @param $form
     */
    private function executeAndRenderQueries($form)
    {
        $sqlStr = $form->getValue('QUERY');

        $queries = array();
        if (stripos($sqlStr, ";") !== false) {
            $queries = explode(";", $sqlStr);
        } else {
            $queries[] = $sqlStr;
        }
        $content = "";

        $this->export->sql = $sqlStr;
        $this->view->exportButton = true;


        if (is_array($queries)) {

            foreach ($queries as $query) {

                //$sql1 = preg_replace('/^SELECT /i', 'SELECT FIRST 1000 ', trim($query));
                $sql1 = trim($query);
                print "<h3>$sql1</h3>";

                if ($results = $this->db->get_results($sql1, ARRAY_N)) {

                    $cols = $this->db->get_col_info();

                    $columns = implode("</td><th>", $cols);

                    $rows = '';
                    foreach ($results as $result) {
                        $result = implode(";", $result);
                        $result = str_replace("\n", "<br>", $result);
                        $result = str_replace("\r", "", $result);
                        $result = str_replace(';', '</td><td>', $result);
                        $rows .= "<tr><td>{$result}</td></tr>";
                    }

                    $content .= "<table class=\"ws_data_table\">
                                            <thead><tr><th>{$columns}</th></tr></thead>
                                            <tbody>
                                                {$rows}
                                            </tbody>
                                           </table>";
                    echo $content;
                }
            }
        }
    }
}