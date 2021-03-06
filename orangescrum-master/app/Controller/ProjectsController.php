<?php

/* * *******************************************************************************
 * Orangescrum Community Edition is a web based Project Management software developed by
 * Orangescrum. Copyright (C) 2013-2017
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Orangescrum, 2059 Camden Ave. #118, San Jose, CA - 95124, US. 
  or at email address support@orangescrum.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * Orangescrum" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by Orangescrum".
 * ****************************************************************************** */

App::uses('AppController', 'Controller');

class ProjectsController extends AppController {

    public $name = 'Projects';
    public $components = array('Format', 'Postcase', 'Tmzone', 'Sendgrid');

    function beforeRender() {
        if (SES_TYPE == 3) {
            //$this->redirect(HTTP_ROOT."dashboard");
        }
        /* if($this->action === 'index') {
          $this->set(	'scaffoldFields', array( 'name', 'short_name', 'isactive', 'dt_created' ) );
          }
          if($this->action === 'view') {
          $this->set(	'scaffoldFields', array( 'name', 'short_name', 'isactive', 'dt_created','dt_updated' ) );
          }
          if($this->action === 'edit') {
          $this->set(	'scaffoldFields', array( 'name', 'short_name') );
          }
          if($this->action === 'add') {
          $this->set(	'scaffoldFields', array( 'name', 'short_name') );
          } */
    }

    function ajax_check_project_exists() {
        $this->layout = 'ajax';

        $this->Project->recursive = -1;

        $name = $this->params['data']['name'];
        $shortname = $this->params['data']['shortname'];

        if (isset($this->params['data']['uniqid'])) {
            $uniqid = $this->params['data']['uniqid'];
            $conditions = array('Project.name' => urldecode($name), 'Project.company_id' => SES_COMP, 'Project.uniq_id !=' => $uniqid);
        } else {
            $conditions = array('Project.name' => urldecode($name), 'Project.company_id' => SES_COMP);
        }

        $chkName = $this->Project->find('first', array('conditions' => $conditions));

        if (isset($chkName['Project']['id']) && $chkName['Project']['id']) {
            echo "Project";
        } else {
            if (isset($this->params['data']['uniqid'])) {
                $uniqid = $this->params['data']['uniqid'];
                $conditions = array('Project.short_name' => urldecode($shortname), 'Project.company_id' => SES_COMP, 'Project.uniq_id !=' => $uniqid);
            } else {
                $conditions = array('Project.short_name' => urldecode($shortname), 'Project.company_id' => SES_COMP);
            }
            $chkShortName = $this->Project->find('first', array('conditions' => $conditions));
            if (isset($chkShortName['Project']['id']) && $chkShortName['Project']['id']) {
                echo "ShortName";
            }
            if ((defined('GINV') && GINV == 1 ) || (defined('DBRD') && DBRD == 1) || (defined('EXP') && EXP == 1)) {
                if (isset($this->params['data']['cust_email']) && !empty($this->params['data']['cust_email']) && isset($this->params['data']['cust_compny']) && !empty($this->params['data']['cust_compny'])) {
                    $this->loadModel('InvoiceCustomer');
                    $conditions = array('OR' => array('email' => trim($this->params['data']['cust_email']), 'organization' => trim($this->params['data']['cust_compny'])));
                    $conditions[] = "company_id=" . SES_COMP;
                    if ($id > 0) {
                        $conditions[] = "id!=$id";
                    }
                    $exist = $this->InvoiceCustomer->find('all', array('conditions' => $conditions));
                    if (is_array($exist) && count($exist) > 0) {
                        echo "CustomerEmail";
                    }
                }
            }
        }
        exit;
    }

    function ajax_edit_project() {
        $this->layout = 'ajax';
        $uniqid = NULL;
        $uname = NULL;
        $projArr = array();
        $getTech = array();

        if (isset($this->request->data['pid']) && $this->request->data['pid']) {
            $uniqid = $this->request->data['pid'];
            $this->loadModel("Project");
            $this->Project->recursive = -1;
            $project_id = $this->Project->getProjectFields(array('uniq_id'=>$uniqid),array('id'));
          /*  $joins =array();
            $fields = array('Project.*');
            #echo "<pre>";print_r($project_id);exit;
            if(define('PT') && PT == 1){
              //  $this->Project->bindModel(array('hasMany' => array('ProjectTemplate.TemplateModuleCase' => array('foreignKey' => 'project_id'))));
               /* $this->Project->bindModel(
                    array('hasMany' => array(
                            'ProjectTemplate.TemplateModuleCase' => array(
                                'className' => 'ProjectTemplate.TemplateModuleCase',
                                'foreignKey' => 'project_id',
                                'conditions' => array('ProjectTemplate.TemplateModuleCase.project_id' => $project_id['Project']['id']
                            )
                        )
                    )
            )); 
                $joins = array(
                    array('table' => 'template_module_cases',
                        'alias' => 'TemplateModuleCase',
                        'type' => 'LEFT',
                        'conditions' => array(
                            'Project.id = TemplateModuleCase.project_id',
                        )
                    )
                );
                $fields = array('Project.*','TemplateModuleCase.*');
                
            } */
            $projArr = $this->Project->find('first', array('conditions' => array('Project.uniq_id' => $uniqid, 'Project.company_id' => SES_COMP)));
             #echo "<pre>";print_r($projArr);exit;
           
            if (count($projArr)) {
                $this->loadModel("User");
                $this->User->recursive = -1;
                $getUser = $this->User->find("first", array('conditions' => array('User.isactive' => 1, 'User.id' => $projArr['Project']['user_id']), 'fields' => array('User.name')));
                if (count($getUser)) {
                    $uname = $getUser['User']['name'];
                }
                if(defined('TSG') && TSG == 1){
                     $this->loadModel("TaskStatusGroup.Workflow");
                     $workflowList = $this->Workflow->getWorkflowList();
                }
                if(defined('PT') && PT == 1){
                    $this->loadModel('ProjectTemplate.TemplateModuleCase');
                    $this->loadModel('ProjectTemplate.ProjectTemplate');
                    $joins = array(
                    array('table' => 'project_templates',
                        'alias' => 'ProjectTemplate',
                        'type' => 'LEFT',
                        'conditions' => array(
                            'TemplateModuleCase.template_module_id = ProjectTemplate.id',
                        )
                    )
                );
               
                    $project_added_template = $this->TemplateModuleCase->find('all',array('conditions'=>array('TemplateModuleCase.project_id'=>$project_id['Project']['id']),'joins'=>$joins,'fields' => array('TemplateModuleCase.template_module_id','ProjectTemplate.module_name')));
                    $projectTemplateList = $this->ProjectTemplate->find('list', array('conditions' => array('ProjectTemplate.company_id' => SES_COMP), 'fields' => array('ProjectTemplate.id','ProjectTemplate.module_name'), 'order' => 'ProjectTemplate.module_name ASC'));
                    $this->set('template_module_id',$project_added_template[0]['TemplateModuleCase']['template_module_id']);
                    $this->set('projectTemplateList',$projectTemplateList);
                    $this->set('template_module_name',$project_added_template[0]['ProjectTemplate']['module_name']);
                    
                    #echo "<pre>";print_r($project_added_template);print_r($projectTemplateList);exit;
                    
                }
            }
        }
        $this->set('uniqid', $uniqid);
        $this->set('uname', $uname);
        $this->set('projArr', $projArr);

        $getProjUsers = $this->Project->query("select User.name,ProjectUser.default_email,User.id,Project.id,ProjectUser.id from project_users as ProjectUser, users as User, projects as Project where User.id=ProjectUser.user_id and Project.uniq_id='" . $_GET['pid'] . "' and Project.id=ProjectUser.project_id and User.isactive='1'");
        $this->set('getProjUsers', $getProjUsers);

        $this->loadModel("Easycase");
        $this->Easycase->recursive = -1;
        $projectTasks = $this->Easycase->find('all', array('conditions' => array('Easycase.project_id' => $projArr['Project']['id'])));
        if (count($projectTasks) > 0) {
            $can_change_workflow = 0;
            if(defined('TSG') && TSG == 1){
                $workflow_name = $this->Workflow->find('first', array('conditions' => array('Workflow.id' => $projArr['Project']['workflow_id']), 'fields' => array('Workflow.name', 'Workflow.id')));
            }    
        } else {
            $can_change_workflow = 1;
        }
        
        $quickMem = $this->Easycase->getMemebers($uniqid, 'default');
        $this->set('quickMem', $quickMem);
        $prj = $this->Project->findByUniqId($uniqid);
        $this->set('defaultAssign', $prj['Project']['default_assign']);
        $this->set('canChangeWorkflow', $can_change_workflow);
        if(defined('TSG') && TSG == 1){
            $this->set('workflowList', $workflowList);
            $this->set('workflow_name', $workflow_name['Workflow']['name']);
            $this->set('workflow_id', $workflow_name['Workflow']['id']);
        }
    }

    function settings($img = null,$sd = '', $sc = '', $api_flag = '') {
//        pr($this->request->data); exit;
        if (!empty($api_flag) && $api_flag == 1) {
            $ses_id = !empty($sd) ? $sd : SES_ID;
            $ses_comp = !empty($sc) ? $sc : SES_COMP;
        } else {
            $ses_id = !empty($sd) ? $sd : SES_ID;
            $ses_comp = !empty($sc) ? $sc : SES_COMP;
            if ($this->request->is('ajax')) {
                $this->layout = "ajax";
            }
        }

        if (isset($this->params['data']['Project'])) {
            $this->loadModel("ProjectUser");
            #echo "<pre>";print_r($this->params['data']['Project']);exit;
            $postProject['Project'] = $this->params['data']['Project'];
            $postProject['Project']['name'] = trim($postProject['Project']['name']);
            $postProject['Project']['short_name'] = trim($postProject['Project']['short_name']);

            if ($postProject['Project']['validateprj'] == 1) {
                $prjid = $postProject['Project']['id'];
                $redirect = HTTP_ROOT . "projects/manage/";
                $page_lmt = $postProject['Project']['pg'];
                if (intval($page_lmt) > 1) {
                    $redirect .= "?page=" . $page_lmt;
                }

                $findName = $this->Project->query("SELECT id FROM projects WHERE name='" . addslashes($postProject['Project']['name']) . "' AND id!=" . $prjid . " AND company_id='" . SES_COMP . "'");
                if (count($findName)) {
                    $this->Session->write("ERROR", __("Project name") . " '" . $postProject['Project']['name'] . "' " . __("already exists"));
                    $this->redirect($redirect);
                }

                $findShrtName = $this->Project->query("SELECT id FROM projects WHERE short_name='" . addslashes($postProject['Project']['short_name']) . "' AND id!=" . $prjid . " AND company_id='" . SES_COMP . "'");
                if (!empty($findShrtName)) {
                    $this->Session->write("ERROR", __("Project short name") . " '" . $postProject['Project']['short_name'] . "' " . __("already exists"));
                    $this->redirect($redirect);
                }

                $postProject['Project']['dt_updated'] = GMT_DATETIME;
                if ($this->Project->save($postProject)) {
                    if (isset($postProject['Project']['module_id']) &&  $postProject['Project']['module_id'] != 0 ) {
                    //Add relation when template is added
                    $post_temp['TemplateModuleCase']['template_module_id'] = $postProject['Project']['module_id'];
                    $post_temp['TemplateModuleCase']['user_id'] = $ses_id;
                    $post_temp['TemplateModuleCase']['company_id'] = $ses_comp;
                    $post_temp['TemplateModuleCase']['project_id'] = $prjid;
                    $s = ClassRegistry::init('ProjectTemplate.TemplateModuleCase')->save($post_temp);
                    $this->loadModel('ProjectTemplate');
                    $options = array('conditions' => array('ProjectTemplate.id' => $postProject['Project']['module_id']), 'recursive' => 2);
                    $all_template_data = $this->ProjectTemplate->find('first', $options);

                    $Easycase = ClassRegistry::init('Easycase');
                    $Easycase->recursive = -1;
                    $CaseActivity = ClassRegistry::init('CaseActivity');
                    $wrkflw_id = $this->Project->find('list', array('conditions' => array('Project.id' => $prjid), 'fields' => array('Project.workflow_id')));
                    $this->loadModel('Status');
                    $status_id = $this->Status->find('first', array('conditions' => array('Status.workflow_id' => $wrkflw_id), 'order' => 'Status.seq_order ASC'));
                    #echo "<pre>";print_r($status_id);exit;
                    $this->loadModel('ProjectUser');
                    $project_user_list = $this->ProjectUser->find('list', array('conditions' => array('ProjectUser.project_id' => $prjid), 'fields' => array('ProjectUser.user_id')));
                    $this->loadModel('Milestone');
                    $tmpDepends = array();
                    foreach ($all_template_data['ProjectTemplateMilestone'] as $key => $value) {
                        $milestone_case = array();
                        if (empty($value['is_default'])) {
                            $mlstn_data = array();
                            $milestone_uniq_id = $this->Format->generateUniqNumber();
                            $mlstn_data = array('uniq_id' => $milestone_uniq_id, 'project_id' => $prjid, 'user_id' => $ses_id, 'company_id' => $ses_comp, 'title' => $value['title'], 'description' => $value['description']);
                            if (!empty($value['start_date'])) {
                                $mlstn_data['start_date'] = $value['start_date'];
                            }
                            if (!empty($value['end_date'])) {
                                $mlstn_data['end_date'] = $value['end_date'];
                            }
                            $milestone_case[] = $mlstn_data;
                            $this->Milestone->saveAll($milestone_case);
                            $mlstId = $this->Milestone->getLastInsertID();
                        }
                        foreach ($value['ProjectTemplateCase'] as $k => $v) {
                            $postCases['Easycase']['uniq_id'] = $this->Format->generateUniqNumber();
                            $postCases['Easycase']['project_id'] = $prjid;
                            $postCases['Easycase']['user_id'] = $ses_id;
                            $postCases['Easycase']['type_id'] = 2;
                            $postCases['Easycase']['priority'] = 1;
                            $postCases['Easycase']['title'] = $v['title'];
                            $postCases['Easycase']['message'] = $v['description'];
                            $postCases['Easycase']['assign_to'] = !empty($v['assign_to']) && in_array($v['assign_to'], $project_user_list) ? $v['assign_to'] : 0;
                            if (defined('TLG') && TLG == 1) {
                                $estimated_hours = $v['estimated_hours'];
                                /* saving in secs */
                                if (strpos($estimated_hours, ':') > -1) {
                                    $split_est = explode(':', $estimated_hours);
                                    $est_sec = ((($split_est[0]) * 60) + intval($split_est[1])) * 60;
                                } else {
                                    $est_sec = $estimated_hours * 3600;
                                }
                                if (defined('TSG') && TSG == 1) {
                                    $status = ClassRegistry::init('Status');
                                    $crnt_status = $status->find('first', array('conditions' => array('id' => $status_id['Status']['id'])));
                                    $postCases['Easycase']['completed'] = $crnt_status['Status']['percentage'];
                                }
                                $estimated_hours = $est_sec;
                                $postCases['Easycase']['estimated_hours'] = $estimated_hours;
                            } else {
                                $postCases['Easycase']['estimated_hours'] = $v['estimated_hours'];
                            }
                            $postCases['Easycase']['due_date'] = $v['end_date'];
                            $postCases['Easycase']['gantt_start_date'] = $v['start_date'];
                            $postCases['Easycase']['depends'] = !empty($v['depends']) ? $v['depends'] : '';
                            $postCases['Easycase']['istype'] = 1;
                            $postCases['Easycase']['format'] = 2;
                            $postCases['Easycase']['status'] = 1;
                            $postCases['Easycase']['legend'] = $status_id['Status']['id'];
                            $postCases['Easycase']['isactive'] = 1;
                            $postCases['Easycase']['dt_created'] = GMT_DATETIME;
                            $postCases['Easycase']['actual_dt_created'] = GMT_DATETIME;
                            $caseNoArr = $Easycase->find('first', array('conditions' => array('Easycase.project_id' => $prjid), 'fields' => array('MAX(Easycase.case_no) as caseno')));
                            $caseNo = $caseNoArr[0]['caseno'] + 1;
                            $postCases['Easycase']['case_no'] = $caseNo;
                            if ($Easycase->saveAll($postCases)) {
                                $caseid = $Easycase->getLastInsertID();
                                if (defined('GTLG') && GTLG == 1) {
                                    if ($postCases['Easycase']['estimated_hours'] != '' && $postCases['Easycase']['gantt_start_date'] != '' && $postCases['Easycase']['assign_to'] != 0) {
                                        $isAssignedUserFree = $this->Postcase->setBookedData($postCases, $postCases['Easycase']['estimated_hours'], $caseid, $ses_comp);
                                        if ($isAssignedUserFree != 1) {
                                            $overloadDataArr = array(
                                                'assignTo' => $postCases['Easycase']['assign_to'],
                                                'caseId' => $caseid,
                                                'caseUniqId' => $postCases['Easycase']['uniq_id'],
                                                'est_hr' => $postCases['Easycase']['estimated_hours'] / 3600,
                                                'projectId' => $postCases['Easycase']['project_id'],
                                                'str_date' => $postCases['Easycase']['gantt_start_date']
                                            );
                                            $response = $this->Format->overloadUsers($overloadDataArr);
                                        }
                                    }
                                }
                                //if($v['depends']!= ''){                                
                                $tmpDepends[$v['id']][0] = $caseid;
                                $tmpDepends[$v['id']][1] = $v['depends'];
                                $tmpDepends[$v['id']][2] = $v['id'];
                                //}
                                if (empty($value['is_default'])) {
                                    $case_milestones[] = array('easycase_id' => $caseid, 'milestone_id' => $mlstId, 'project_id' => $prjid, 'user_id' => $ses_id);
                                }
                                $CaseActivity->recursive = -1;
                                $CaseAct['easycase_id'] = $caseid;
                                $CaseAct['user_id'] = $ses_id;
                                $CaseAct['project_id'] = $prjid;
                                $CaseAct['case_no'] = $caseNo;
                                $CaseAct['type'] = 1;
                                $CaseAct['dt_created'] = GMT_DATETIME;
                                $CaseActivity->saveAll($CaseAct);
                            }
                        }
                    }
                    //  print_r($tmpDepends);exit;
                    if (!empty($tmpDepends)) {
                        foreach ($tmpDepends as $k => $v) {
                            if (!empty($v[1])) {
                                $Easycase->id = $v[0];
                                $Easycase->saveField('depends', $tmpDepends[$v[1]][0]);
                            }
                        }
                    }
                    $this->loadModel('EasycaseMilestone');
                    $this->EasycaseMilestone->saveAll($case_milestones);
                }
                    
                    $this->Session->write("SUCCESS", "'" . strip_tags($postProject['Project']['name']) . "' " . __("saved successfully"));
                    $this->redirect($redirect);
                }
            } else {
                //$this->redirect(HTTP_ROOT."projects/settings/?pid=".$postProject['Project']['uniq']);
            }
        }


        /* $uniqid = NULL; $uname = NULL;
          $projArr = array(); $getTech = array();
          if(isset($_GET['pid']) && $_GET['pid']) {
          $uniqid = $_GET['pid'];
          $this->Project->recursive = -1;
          //$uniqid = Sanitize::clean($uniqid, array('encode' => false));
          $projArr = $this->Project->find('first', array('conditions' => array('Project.uniq_id'=>$uniqid,'Project.company_id'=>SES_COMP)));
          if(count($projArr))
          {
          $User = ClassRegistry::init('User');
          $User->recursive = -1;
          $getUser = $User->find("first",array('conditions'=>array('User.isactive'=>1,'User.id'=>$projArr['Project']['user_id']),'fields'=>array('User.name')));
          if(count($getUser)){
          $uname = $getUser['User']['name'];
          }

          $Technology = ClassRegistry::init('Technology');
          $getTech = $Technology->find("all",array('conditions'=>array('Technology.name'<>'')));
          }else{
          $this->redirect(HTTP_ROOT."projects/gridview/");
          }
          }
          $this->set('getTech',$getTech);
          $this->set('projArr',$projArr);
          $this->set('uniqid',$uniqid);
          $this->set('uname',$uname);
          This multi section is commenting is due to:
          implement in ajax_edit_project() in ajax.
         */

        /* $getProjUsers = $this->Project->query("select User.name,ProjectUser.default_email,User.id,Project.id,ProjectUser.id from project_users as ProjectUser, users as User, projects as Project where User.id=ProjectUser.user_id and Project.uniq_id='".$_GET['pid']."' and Project.id=ProjectUser.project_id and User.isactive='1'");
          $this->set('getProjUsers',$getProjUsers);

          $this->loadModel("Easycase");
          $this->Easycase->recursive = -1;
          $quickMem = $this->Easycase->getMemebers($_GET['pid'],'default');
          $this->set('quickMem',$quickMem);
          $prj = $this->Project->findByUniqId($uniqid);
          $defaultAssign = $prj['Project']['default_assign'];
          $this->set('defaultAssign',$defaultAssign); */
    }

    function manage($projtype = NULL) {
        $page_limit = 20;
        if ($projtype == 'inactive') {
            $page_limit = 20;
        }
        if ($projtype == 'inactive-grid' || $projtype == 'active-grid') {
            $page_limit = 10;
        }
        $this->Project->recursive = -1;
        $pjid = NULL;
        if (isset($_GET['id']) && $_GET['id']) {
            $pjid = $_GET['id'];
        }
        if (isset($_GET['proj_srch']) && $_GET['proj_srch']) {
            $pjname = htmlentities(strip_tags($_GET['proj_srch']));
            $this->set('prjsrch', 'project search');
        }
        if (isset($_GET['page']) && $_GET['page']) {
            $page = $_GET['page'];
        }
        if (trim($pjid)) {
            $project = "Project";
            $getProj = $this->Project->find('first', array('conditions' => array('Project.id' => $pjid, 'Project.company_id' => SES_COMP), 'fields' => array('Project.name', 'Project.id')));
            if (isset($getProj['Project']['name']) && $getProj['Project']['name']) {
                $project = $getProj['Project']['name'];
            }
            if ($getProj['Project']['id']) {
                if (isset($_GET['action']) && $_GET['action'] == "activate") {
                    $this->Project->query("UPDATE projects SET isactive='1' WHERE id=" . $getProj['Project']['id']);
                    $this->Session->write("SUCCESS", "'" . $project . "' " . __("activated successfully"));
                    $this->redirect(HTTP_ROOT . "projects/manage/");
                }
                if (isset($_GET['action']) && $_GET['action'] == "delete") {
                    $this->Project->query("DELETE FROM projects WHERE id=" . $getProj['Project']['id']);

                    $ProjectUser = ClassRegistry::init('ProjectUser');
                    $ProjectUser->recursive = -1;
                    $ProjectUser->query("DELETE FROM project_users WHERE project_id=" . $getProj['Project']['id']);

                    $this->Session->write("SUCCESS", "'" . $project . "' " . __("deleted successfully"));
                    $this->redirect(HTTP_ROOT . "projects/manage/");
                }
                if (isset($_GET['action']) && $_GET['action'] == "deactivate") {
                    $this->Project->query("UPDATE projects SET isactive='2' WHERE id=" . $getProj['Project']['id']);
                    $this->Session->write("SUCCESS", "'" . $project . "' " . __("deactivated successfully"));
                    $this->redirect(HTTP_ROOT . "projects/manage/inactive");
                }
            } else {
                $this->Session->write("ERROR", __("Invalid or Wrong action!"));
                $this->redirect(HTTP_ROOT . "projects/manage");
            }
        }

        $action = "";
        $uniqid = "";
        $query = "";
        if (isset($_GET['uniqid']) && $_GET['uniqid']) {
            $uniqid = $_GET['uniqid'];
        }

        if ($projtype == "inactive" || $projtype == "inactive-grid") {
            $query = "AND Project.isactive='2'";
        } else {
            $query = "AND Project.isactive='1'";
        }
        if (isset($_GET['project']) && $_GET['project']) {
            $query .= " AND Project.uniq_id='" . $_GET['project'] . "'";
        }
        $query .= " AND Project.company_id='" . SES_COMP . "'";
        if (isset($_GET['action']) && $_GET['action']) {
            $action = $_GET['action'];
        }
        $page = 1;
        $pageprev = 1;
        if (isset($_GET['page']) && $_GET['page']) {
            $page = $_GET['page'];
        }
        if ($projtype != "active-grid" && $projtype != "inactive-grid") {
            $limit1 = $page * $page_limit - $page_limit;
            $limit2 = $page_limit;
            $limit = "LIMIT $limit1,$limit2";
        } else {
            $limit = '';
        }

        $prjselect = $this->Project->query("SELECT name FROM projects AS Project WHERE name!='' " . $query . " ORDER BY name ASC");
        $arrprj = array();
        foreach ($prjselect as $pjall) {
            if (isset($pjall['Project']['name']) && !empty($pjall['Project']['name'])) {
                array_push($arrprj, substr(trim($pjall['Project']['name']), 0, 1));
            }
        }
        if (isset($_GET['prj']) && $_GET['prj']) {
            //$_GET['prj'] = Sanitize::clean($_GET['prj'], array('encode' => false));
            $_GET['prj'] = chr($_GET['prj']);
            $pj = $_GET['prj'] . "%";
            $query .= " AND Project.name LIKE '" . addslashes($pj) . "'";
        }
        $wfl_cndn = '';
        $tlg_cond = '';
        if(defined('TLG') && TLG == 1){
            $tlg_cond = "(select ROUND(SUM(total_hours)) as hours from log_times where log_times.project_id = Project.id) as totalhours";
        } else {
            $tlg_cond = "(select ROUND(SUM(easycases.hours), 1) as hours from easycases where easycases.project_id=Project.id and easycases.reply_type='0' and easycases.isactive='1') as totalhours" ;
        }
        if (defined('TSG') && TSG == 1) {
            $wfl_cndn = ',workflow_id, (select workflows.name from workflows where workflows.id = Project.workflow_id) as wname';
        }

        $all_assigned_proj = null;
        $user_cnd = '';
        if (SES_TYPE == 3) {
            $all_assigned_proj = $this->Project->query('SELECT project_id FROM project_users WHERE user_id=' . $this->Auth->user('id') . ' AND company_id=' . SES_COMP);
            if ($all_assigned_proj) {
                $all_assigned_proj = Hash::extract($all_assigned_proj, '{n}.project_users.project_id');
                $all_assigned_proj = array_unique($all_assigned_proj);
                $query .= " AND (Project.user_id=" . $this->Auth->user('id') . " OR Project.id IN(" . implode(',', $all_assigned_proj) . "))";
                $user_cnd = " AND (Project.user_id=" . $this->Auth->user('id') . " OR Project.id IN(" . implode(',', $all_assigned_proj) . "))";
            } else {
                $query .= " AND Project.user_id=" . $this->Auth->user('id');
                $user_cnd = " AND Project.user_id=" . $this->Auth->user('id');
            }
        }
        if (SES_TYPE == 3) {
            //$query .= " AND Project.user_id=" . $this->Auth->user('id');
            if ($pjname) {
                $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS Project.*" . $wfl_cndn . ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,".$tlg_cond.",(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
	and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size FROM case_files WHERE case_files.project_id=Project.id) AS storage_used ,Status.status as project_status FROM projects AS Project LEFT JOIN project_statuses AS Status ON Project.status_id= Status.id WHERE Project.name!='' " . $query . " and name LIKE '%" . addslashes($pjname) . "%' ORDER BY dt_created DESC $limit");
            } else {
                $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS Project.*" . $wfl_cndn . ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,".$tlg_cond.",(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
	and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size FROM case_files WHERE case_files.project_id=Project.id) AS storage_used ,Status.status as project_status FROM projects AS Project LEFT JOIN project_statuses AS Status ON Project.status_id= Status.id WHERE Project.name!='' " . $query . " ORDER BY dt_created DESC $limit");
            }
        } else {
            if ($pjname) {
                $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS  Project.*," . $wfl_cndn . ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,".$tlg_cond.",(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
	and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size  FROM case_files WHERE case_files.project_id=Project.id) AS storage_used,Status.status as project_status FROM projects AS Project LEFT JOIN project_statuses AS Status ON Project.status_id= Status.id WHERE name!='' " . $query . " and name LIKE '%" . addslashes($pjname) . "%' ORDER BY dt_created DESC $limit ");
            } else {
                $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS Project.*" . $wfl_cndn . ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,".$tlg_cond.",(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
	and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size  FROM case_files WHERE case_files.project_id=Project.id) AS storage_used,Status.status as project_status FROM projects AS Project LEFT JOIN project_statuses AS Status ON Project.status_id= Status.id WHERE name!='' " . $query . " ORDER BY dt_created DESC $limit");
            }
        }
        $tot = $this->Project->query("SELECT FOUND_ROWS() as total");
        $CaseCount = $tot[0][0]['total'];
        if ($prjAllArr) {
            $this->loadModel("User");
            $results_user_ids = Hash::extract($prjAllArr, '{n}.Project.user_id');
            $results_user_ids = array_unique($results_user_ids);
            $results_users = $this->User->find('list', array('conditions' => array('User.id' => $results_user_ids), 'fields' => array('User.id', 'User.name')));
            $this->set('user_names', $results_users);
        }


        $active_project_cnt = 0;
        $inactive_project_cnt = 0;
        if (SES_TYPE == 3) {
            if ($pjname) {
                $grpcount = $this->Project->query('SELECT count(Project.id) as prjcnt, Project.isactive FROM projects AS Project WHERE 1 ' . $user_cnd . ' AND Project.name!="' . $query . '" AND Project.name LIKE "%' . addslashes($pjname) . '%" AND Project.company_id=' . SES_COMP . ' GROUP BY Project.isactive');
            } else {
                $grpcount = $this->Project->query('SELECT count(Project.id) as prjcnt, Project.isactive FROM projects AS Project WHERE 1 ' . $user_cnd . ' AND Project.company_id=' . SES_COMP . ' GROUP BY Project.isactive');
            }
        } else {
            if ($pjname) {
                $grpcount = $this->Project->query('SELECT count(Project.id) as prjcnt, Project.isactive FROM projects AS Project WHERE Project.company_id=' . SES_COMP . ' AND Project.name!="' . $query . '" AND Project.name LIKE "%' . addslashes($pjname) . '%" GROUP BY Project.isactive');
            } else {
                $grpcount = $this->Project->query('SELECT count(Project.id) as prjcnt, Project.isactive FROM projects AS Project WHERE Project.company_id=' . SES_COMP . ' GROUP BY Project.isactive');
            }
        }
        if ($grpcount) {
            foreach ($grpcount AS $key => $val) {
                if ($val['Project']['isactive'] == 1) {
                    $active_project_cnt = $val['0']['prjcnt'];
                } elseif ($val['Project']['isactive'] == 2) {
                    $inactive_project_cnt = $val['0']['prjcnt'];
                }
            }
        }
        $this->set('inactive_project_cnt', $inactive_project_cnt);
        $this->set('active_project_cnt', $active_project_cnt);

        $this->set('caseCount', $tot[0][0]['total']);
        $this->set(compact('data'));
        $this->set('total_records', $prjAllArr);
        $this->set('proj_srch', $pjname);
        $this->set('page_limit', $page_limit);
        $this->set('page', $page);
        $this->set('pageprev', $pageprev);
        $count_grid = count($prjAllArr);
        $this->set('count_grid', $count_grid);
        $this->set('prjAllArr', $prjAllArr);
        $this->set('projtype', $projtype);
        $this->set('action', $action);
        $this->set('uniqid', $uniqid);
        $this->set('arrprj', $arrprj);
        $this->set('page_limit', $page_limit);
        $this->set('casePage', $page);
        if ($projtype == "active-grid" || $projtype == "inactive-grid") {
            $this->render('manage_grid');
        }
    }

    function details($uniq_id = NULL) {

        if ($uniq_id == '') {
            // echo "<pre>";print_r($this->request->data);exit;
            if ($this->request->data['Project']['id']) {
                $projDet = $this->Project->find('first', array('conditions' => array('Project.id' => $this->request->data['Project']['id'], 'Project.uniq_id' => $this->request->data['Project']['uniq_id'])));
                if (!empty($projDet)) {

                    $view = new View($this);
                    $tz = $view->loadHelper('Tmzone');
                    $prjid=$projDet['Project']['id'] ;
                    $projDet['Project']['name'] = $this->request->data['Project']['name'];
                    $projDet['Project']['short_name'] = $this->request->data['Project']['short_name'];
                    $projDet['Project']['estimated_hours'] = $this->request->data['Project']['estimated_hours'];
                    $projDet['Project']['start_date'] = $this->request->data['Project']['start_date'];
                    $projDet['Project']['end_date'] = $this->request->data['Project']['end_date'];
                    $projDet['Project']['budget'] = $this->request->data['Project']['budget'];
                    $projDet['Project']['cost_approved'] = $this->request->data['Project']['cost_approved'];
                    //$projDet['Project']['contingency_fund'] = $this->request->data['Project']['contingency_fund'];
                    $projDet['Project']['status_id'] = $this->request->data['Project']['status_id'];
                    $projDet['Project']['type_id'] = $this->request->data['Project']['type_id'];
                    $projDet['Project']['technology_id'] = $this->request->data['Project']['technology_id'];
                    $projDet['Project']['manager'] = $this->request->data['Project']['manager'];
                    $projDet['Project']['hourly_rate'] = $this->request->data['Project']['hourly_rate'];
                    $projDet['Project']['min_range_percent'] = $this->request->data['Project']['min_range_percent'];
                    $projDet['Project']['max_range_percent'] = $this->request->data['Project']['max_range_percent'];
                    $projDet['Project']['currency'] = $this->request->data['Project']['currency'];
                    $projDet['Project']['description'] = $this->request->data['Project']['description'];
                    $projDet['Project']['industry_type'] = $this->request->data['Project']['industry_type'];

                    if (!empty($projDet['Project']['estimated_hours']) && !empty($projDet['Project']['start_date']) && empty($projDet['Project']['end_date'])) {
                        $end_no_days = ceil($postProject['Project']['estimated_hours'] / $GLOBALS['company_work_hour']);
                        $newdate = strtotime('+' . $end_no_days . ' day', strtotime($projDet['Project']['start_date']));
                        $projDet['Project']['end_date'] = date('Y-m-d', $newdate);
                    }
                    if (empty($projDet['Project']['estimated_hours']) && !empty($projDet['Project']['start_date']) && !empty($projDet['Project']['end_date'])) {
                        $project_duration = $this->Format->getProjectDuration($projDet['Project']['start_date'], $projDet['Project']['end_date']);
                        $estimate_hr = (int) ($project_duration * $GLOBALS['company_work_hour']);
                        $postProject['Project']['estimated_hours'] = $estimate_hr;
                    }
                    if (isset($this->request->data['Project']['add_customer']) && $this->request->data['Project']['add_customer'] == 'add_customer' && $this->request->data['Project']['invoice_customer_id'] == 'add') {
                        $post_customer['Customer'] = $this->data['Customer'];
                        $post_customer['Customer']['cust_currency'] = $postProject['Project']['currency'];

                        $user_id = $this->add_prj_customer($post_customer['Customer']);
                        if (count($user_id > 0)) {
                            $this->loadModel('ProjectUser');
                            $ProjUsr['ProjectUser']['id'] = $lastid;
                            $ProjUsr['ProjectUser']['project_id'] = $prjid;
                            $ProjUsr['ProjectUser']['user_id'] = $user_id[0];
                            $ProjUsr['ProjectUser']['company_id'] = SES_COMP;
                            $ProjUsr['ProjectUser']['default_email'] = 1;
                            $ProjUsr['ProjectUser']['istype'] = 1;
                            $ProjUsr['ProjectUser']['dt_visited'] = GMT_DATETIME;
                            $this->ProjectUser->saveAll($ProjUsr);
                            $lastid = $lastid + 1;
                            if ($this->Auth->user('id') != $user_id[0]) {
                                $this->generateMsgAndSendPjMail($prjid, $user_id[0], $comp);
                            }
                            $projDet['Project']['invoice_customer_id'] = $user_id[1];
                        } else {
                            $projDet['Project']['invoice_customer_id'] = 0;
                        }
                    } else {
                        $projDet['Project']['invoice_customer_id'] = $this->request->data['Project']['invoice_customer_id'];
                    }
                    #echo "<pre>";print_r($projDet);exit;
                    if ($this->Project->save($projDet)) {
                        $this->Session->write("SUCCESS", __("Project details saved successfully."));
                        $this->redirect(HTTP_ROOT . "projects/details/" . $this->request->data['Project']['uniq_id']);
                    } else {
                        $this->Session->write("SUCCESS", __("Could not save details."));
                        $this->redirect(HTTP_ROOT . "projects/details/" . $this->request->data['Project']['uniq_id']);
                    }
                } else {
                    $this->Session->write("SUCCESS", __("No such project found."));
                    $this->redirect(HTTP_ROOT . "projects/details/" . $this->request->data['Project']['uniq_id']);
                }
            } else {
                $this->redirect(HTTP_ROOT . "projects/manage/");
            }
        } else {
            $this->loadModel('Currency');
            /* $currency_details = $this->Currency->find('list',array('conditions'=>array('status'=>"Active"),'fields' => array("Currency.code","Currency.name"),'ORDER'=>'Currency.id ASC'));
              //echo "<pre>";print_r($currency_details);exit;
              $this->set('currency_details',$currency_details); */
            $proj_details = $this->Project->find('first', array('conditions' => array('Project.uniq_id' => $uniq_id)));
            $this->set('projectdata', $proj_details);
        }
    }

    /*
     * Author Satyajeet
     * TO save new project Status
     */

    function saveNewStatus() {
        $status = $this->request->data['status'];
        $this->loadModel('ProjectStatus');
        $data = array();
        $existing = $this->ProjectStatus->find('first', array('conditions' => array('ProjectStatus.status' => $status, 'ProjectStatus.company_id' => array(SES_COMP, 0))));
        if (!empty($existing)) {
            $data['error'] = 1;
            $data['msg'] = 'This Project Status already exists.';
        } else {
            $techData = array();
            $techData['status'] = $status;
            $techData['company_id'] = SES_COMP;
            $techData['dt_created'] = GMT_DATETIME;
            if ($this->ProjectStatus->save($techData)) {
                $data['success'] = 1;
                $data['id'] = $this->ProjectStatus->getLastInsertId();
                $data['msg'] = 'Status is saved successfully';
            } else {
                $data['error'] = 1;
                $data['msg'] = 'Could not save Status. Please try again.';
            }
        }
        echo json_encode($data);
        exit;
    }

    /*
     * Author Satyajeet
     * TO save new project Type
     */

    function saveNewType() {
        $type = $this->request->data['type'];
        $this->loadModel('ProjectType');
        $data = array();
        $existing = $this->ProjectType->find('first', array('conditions' => array('ProjectType.type' => $type, 'ProjectType.company_id' => array(SES_COMP, 0))));
        if (!empty($existing)) {
            $data['error'] = 1;
            $data['msg'] = 'This Project Type already exists.';
        } else {
            $techData = array();
            $techData['type'] = $type;
            $techData['company_id'] = SES_COMP;
            $techData['dt_created'] = GMT_DATETIME;
            if ($this->ProjectType->save($techData)) {
                $data['success'] = 1;
                $data['id'] = $this->ProjectType->getLastInsertId();
                $data['msg'] = 'Project Type is saved successfully';
            } else {
                $data['error'] = 1;
                $data['msg'] = 'Could not save Project Type. Please try again.';
            }
        }
        echo json_encode($data);
        exit;
    }

    /*
     * Author Satyajeet
     * TO save new project Technology
     */

    function saveNewTech() {
        $technology = $this->request->data['technology'];
        $this->loadModel('ProjectTechnology');
        $data = array();
        $this->ProjectTechnology->recursive = -1;
        $existing = $this->ProjectTechnology->find('first', array('conditions' => array('ProjectTechnology.technology' => $technology, 'ProjectTechnology.company_id' => array(SES_COMP, 0))));
        if (!empty($existing)) {
            $data['error'] = 1;
            $data['msg'] = 'This Project Technology already exists.';
        } else {
            $techData = array();
            $techData['technology'] = $technology;
            $techData['company_id'] = SES_COMP;
            $techData['dt_created'] = GMT_DATETIME;
            if ($this->ProjectTechnology->save($techData)) {
                $data['success'] = 1;
                $data['id'] = $this->ProjectTechnology->getLastInsertId();
                $data['msg'] = 'Project Technology is saved successfully';
            } else {
                $data['error'] = 1;
                $data['msg'] = 'Could not save Project Technology. Please try again.';
            }
        }
        echo json_encode($data);
        exit;
    }

    function add_project($createProject = '', $sd = '', $sc = '', $api_flag = '') {
//        pr($this->request->data); exit;
        if (!empty($api_flag) && $api_flag == 1) {
            $ses_id = !empty($sd) ? $sd : SES_ID;
            $ses_comp = !empty($sc) ? $sc : SES_COMP;
        } else {
            $ses_id = !empty($sd) ? $sd : SES_ID;
            $ses_comp = !empty($sc) ? $sc : SES_COMP;
            if ($this->request->is('ajax')) {
                $this->layout = "ajax";
            }
        }
        $Company = ClassRegistry::init('Company');
        $comp = $Company->find('first', array('fields' => array('Company.name', 'Company.id', 'Company.uniq_id')));
        $userscls = ClassRegistry::init('User');
        $companyusercls = ClassRegistry::init('CompanyUser');
        if (!empty($api_flag) && $api_flag == 1) {
            $postProject['Project'] = $createProject['Project'];
        } else if (!empty($createProject)) {
            $postProject['Project'] = $createProject['Project'];
        } else {
            if ($this->request->is('ajax')) {
                $postProject['Project'] = $this->data['Project'];
                $postProject['Project']['validate'] = 1;
            } else {
                $postProject['Project'] = $this->params['data']['Project'];
            }
            if (!empty($this->params['data']['Project']['start_date'])) {
                $postProject['Project']['start_date'] = date("Y-m-d", strtotime($this->params['data']['Project']['start_date']));
            }
            if (!empty($this->params['data']['Project']['end_date'])) {
                $postProject['Project']['end_date'] = date("Y-m-d", strtotime($this->params['data']['Project']['end_date']));
            }
        }
        if ((isset($this->data['Project']['members_list']) && $this->data['Project']['members_list']) || (isset($createProject['Project']['members_list']) && $createProject['Project']['members_list'])) {
            if (!empty($createProject['Project']['members_list'])) {
                $emaillist = trim(trim($createProject['Project']['members_list']), ',');
            } else {
                $emaillist = trim(trim($this->data['Project']['members_list']), ',');
            }
            if (strstr(trim($emaillist), ',')) {
                $emailid = explode(',', $emaillist);
            } else {
                $emailid = explode(',', $emaillist);
            }
            $emailarr = '';
            foreach ($emailid AS $ind => $data) {
                if (trim($data) != '') {
                    $emailarr[$ind] = trim($data);
                    $cond .= " (email LIKE '%" . trim($data) . "%') OR";
                }
            }
            //print_r($emailarr);exit;
            if ($emailarr != '') {
                $emailarr = array_unique($emailarr);
                $cond = substr($cond, 0, strlen($cond) - 2);
                $userlist = $userscls->find('list', array('conditions' => array($cond), 'fields' => array('id', 'email')));
                if ($userlist) {
                    $compuserlist = $companyusercls->find('list', array('conditions' => array('company_id' => $ses_comp, 'user_id' => array_keys($userlist), 'is_active' => 1), 'fields' => array('CompanyUser.id', 'CompanyUser.user_id')));
                    if ($compuserlist) {
                        foreach ($compuserlist AS $k1 => $value) {
                            $postProject['Project']['members'][] = $value;
                            $removeduserlist[] = $userlist[$value];
                            //$index = array_search($userlist[$value],$emailarr);
                            //unset($emailarr[$index]);
                        }
                        foreach ($emailarr AS $key1 => $edata) {
                            if (in_array(trim($edata), $removeduserlist)) {
                                unset($emailarr[$key1]);
                            }
                        }
                    }
                }
            }
        }
        $memberslist = '';

        if ($postProject['Project']['members']) {
            $memberslist = array_unique($postProject['Project']['members']);
        } elseif (!$GLOBALS['project_count']) {
            $memberslist[] = $ses_id;
        }
        if (empty($memberslist)) {
            $memberslist[] = $ses_id;
        }
        if ((isset($this->params['data']['Project']) || $createProject['Project']) && $postProject['Project']['validate'] == 1) {
            $findName = $this->Project->find('first', array('conditions' => array('Project.name' => $postProject['Project']['name'], 'Project.company_id' => $ses_comp), 'fields' => array('Project.id')));
            if ($findName) {
                if (!empty($api_flag) && $api_flag == 1) {
                    $d['status'] = "ERROR";
                    $d['message'] = "Project name " . $postProject['Project']['name'] . " already exists";
                    return json_encode($d);
                } else {
                    if ($this->request->is('ajax')) {
                        $d['status'] = "ERROR";
                        $d['message'] = "Project name '" . $postProject['Project']['name'] . "' already exists";
                        echo json_encode($d);
                        exit;
                    } else {
                        $this->Session->write("ERROR", __("Project name") . " '" . $postProject['Project']['name'] . "' " . __("already exists"));
                        if (empty($createProject)) {
                        $this->redirect(HTTP_ROOT . "projects/manage/");
                    }
                }
            }
            }
            $findShrtName = $this->Project->find('first', array('conditions' => array('Project.short_name' => $postProject['Project']['short_name'], 'Project.company_id' => $ses_comp), 'fields' => array('Project.id')));
            if ($findShrtName) {
                if (!empty($api_flag) && $api_flag == 1) {
                    $d['status'] = "ERROR";
                    $d['message'] = "Project short name " . $postProject['Project']['short_name'] . " already exists";
                    return json_encode($d);
                } else {
                    if ($this->request->is('ajax')) {
                        $d['status'] = "ERROR";
                        $d['message'] = "Project short name '" . $postProject['Project']['short_name'] . "' already exists";
                        echo json_encode($d);
                        exit;
                    } else {
                        $this->Session->write("ERROR", __("Project short name") . " '" . $postProject['Project']['short_name'] . "' " . __("already exists"));
                        if (empty($createProject)) {
                        $this->redirect(HTTP_ROOT . "projects/manage/");
                    }
                }
            }
            }

            $postProject['Project']['uniq_id'] = trim($postProject['Project']['name']);
            $postProject['Project']['short_name'] = trim($postProject['Project']['short_name']);

            $prjUniqId = md5(uniqid());
            $postProject['Project']['uniq_id'] = $prjUniqId;
            $postProject['Project']['user_id'] = $ses_id;
            $postProject['Project']['project_type'] = 1;
            if (isset($postProject['Project']['default_assign']) && !empty($postProject['Project']['default_assign'])) {
                $postProject['Project']['default_assign'] = $postProject['Project']['default_assign'];
            } else {
                $postProject['Project']['default_assign'] = $ses_id;
            }
            $postProject['Project']['isactive'] = 1;
            $postProject['Project']['name'] = trim($postProject['Project']['name']);
            $postProject['Project']['dt_created'] = GMT_DATETIME;
            $postProject['Project']['company_id'] = $ses_comp;
            $postProject['Project']['description'] = trim($postProject['Project']['description']);


            if (!empty($postProject['Project']['estimated_hours']) && !empty($postProject['Project']['start_date']) && empty($postProject['Project']['end_date'])) {
                $end_no_days = ceil($postProject['Project']['estimated_hours'] / $GLOBALS['company_work_hour']);
                // $postProject['Project']['end_date'] = date('Y-m-d', strtotime($postProject['Project']['start_date']." + ".$end_no_days." days"));              
                $newdate = strtotime('+' . $end_no_days . ' day', strtotime($postProject['Project']['start_date']));
                $postProject['Project']['end_date'] = date('Y-m-d', $newdate);
            }
            if (empty($postProject['Project']['estimated_hours']) && !empty($postProject['Project']['start_date']) && !empty($postProject['Project']['end_date'])) {
                $project_duration = $this->Format->getProjectDuration($postProject['Project']['start_date'], $postProject['Project']['end_date']);
                $estimate_hr = (int) ($project_duration * $GLOBALS['company_work_hour']);
                $postProject['Project']['estimated_hours'] = $estimate_hr;
            }

            if (isset($postProject['Project']['invoice_customer_id']) && $postProject['Project']['invoice_customer_id'] != 'add') {

                $postProject['Project']['invoice_customer_id'] = $postProject['Project']['invoice_customer_id'];
            }
            if ($this->Project->saveAll($postProject)) {
                $prjid = $this->Project->getLastInsertID();

                $User = ClassRegistry::init('User');
                $User->recursive = -1;
                //$adminArr = $User->find("all",array('conditions'=>array('User.isactive'=>1,'User.istype'=>1),'fields'=>array('User.id')));

                $ProjectUser = ClassRegistry::init('ProjectUser');
                $ProjectUser->recursive = -1;
                $getLastId = $ProjectUser->find('first', array('fields' => array('ProjectUser.id'), 'order' => array('ProjectUser.id' => 'DESC')));
                $lastid = $getLastId['ProjectUser']['id'] + 1;
                if (((defined('GINV') && GINV == 1) || (defined('EXP') && EXP == 1) || (defined('DBRD') && DBRD == 1) ) && isset($postProject['Project']['add_customer']) && $postProject['Project']['add_customer'] == 'add_customer' && $postProject['Project']['invoice_customer_id'] == 'add') {
                    if (!empty($createProject['Customer'])) {
                        $post_customer['Customer'] = $createProject['Customer'];
                        $post_customer['Customer']['cust_currency'] = $postProject['Project']['currency'];
                    } else {
                    $post_customer['Customer'] = $this->data['Customer'];
                    $post_customer['Customer']['cust_currency'] = $postProject['Project']['currency'];
                    }
                    try {
                        $user_id = $this->add_prj_customer($post_customer['Customer'], $prjid);
                        if (count($user_id > 0)) {
                            if (!$this->data['Customer']['status'] && $this->data['Customer']['status'] != "allow") {
                                $ProjUsr['ProjectUser']['id'] = $lastid;
                                $ProjUsr['ProjectUser']['project_id'] = $prjid;
                                $ProjUsr['ProjectUser']['user_id'] = $user_id[0];
                                $ProjUsr['ProjectUser']['company_id'] = $ses_comp;
                                $ProjUsr['ProjectUser']['default_email'] = 1;
                                $ProjUsr['ProjectUser']['istype'] = 1;
                                $ProjUsr['ProjectUser']['dt_visited'] = GMT_DATETIME;
                                $ProjectUser->saveAll($ProjUsr);
                                $lastid = $lastid + 1;
                                if ((!empty($this->Auth->user('id')) && $this->Auth->user('id') != $user_id[0]) || $ses_id != $user_id[0]) {
                                    $this->generateMsgAndSendPjMail($prjid, $user_id[0], $comp, $ses_id);
                                }
                            }
                            $this->Project->id = $prjid;
                            $this->Project->saveField('invoice_customer_id', $user_id[1]);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                if (!empty($memberslist)) {
                    foreach ($memberslist as $members) {
                        if (defined('ROLE') && ROLE == 1) {
                            $CompanyUser = ClassRegistry::init('CompanyUser');
                            $roleId = $CompanyUser->find('first', array('conditions' => array('CompanyUser.company_id' => SES_COMP, 'CompanyUser.user_id' => $members), 'fields' => 'CompanyUser.role_id'));
                        }
                        $ProjUsr['ProjectUser']['id'] = $lastid;
                        $ProjUsr['ProjectUser']['project_id'] = $prjid;
                        $ProjUsr['ProjectUser']['user_id'] = $members;
                        $ProjUsr['ProjectUser']['company_id'] = $ses_comp;
                        $ProjUsr['ProjectUser']['default_email'] = 1;
                        $ProjUsr['ProjectUser']['istype'] = 1;
                        if (defined('ROLE') && ROLE == 1) {
                            $ProjUsr['ProjectUser']['role_id'] = !empty($roleId['CompanyUser']['role_id']) ? $roleId['CompanyUser']['role_id'] : 3;
                        }
                        $ProjUsr['ProjectUser']['dt_visited'] = GMT_DATETIME;
                        $ProjectUser->saveAll($ProjUsr);
                        if (defined('ROLE') && ROLE == 1) {
                            $roleAction = ClassRegistry::init('RoleAction');
                            $projectAction = ClassRegistry::init('ProjectAction');
                            $prjroleId = !empty($roleId['CompanyUser']['role_id']) ? $roleId['CompanyUser']['role_id'] : 3;
                            $actions = $roleAction->find('all', array('conditions' => array('RoleAction.company_id' => SES_COMP, 'RoleAction.role_id' => $prjroleId), 'fields' => array('RoleAction.role_id', 'RoleAction.action_id', 'RoleAction.is_allowed')));
                            if (!empty($actions)) {
                                foreach ($actions as $k => $action) {
                                    $action['ProjectAction'] = $action['RoleAction'];
                                    $action['ProjectAction']['project_id'] = $prjid;
                                    unset($action['RoleAction']);
                                    $projectAction->saveAll($action);
                                }
                            }
                        }
                        $lastid = $lastid + 1;
                        if ((!empty($this->Auth->user('id')) && $this->Auth->user('id') != $members) || $ses_id != $members) {
                            $this->generateMsgAndSendPjMail($prjid, $members, $comp, $ses_id);
                        }
                    }
                }



                if (isset($postProject['Project']['module_id']) && isset($prjid) && $postProject['Project']['module_id']) {
                    //Add relation when template is added
                    $post_temp['TemplateModuleCase']['template_module_id'] = $postProject['Project']['module_id'];
                    $post_temp['TemplateModuleCase']['user_id'] = $ses_id;
                    $post_temp['TemplateModuleCase']['company_id'] = $ses_comp;
                    $post_temp['TemplateModuleCase']['project_id'] = $prjid;
                    $s = ClassRegistry::init('ProjectTemplate.TemplateModuleCase')->save($post_temp);
                    $this->loadModel('ProjectTemplate');
                    $options = array('conditions' => array('ProjectTemplate.id' => $postProject['Project']['module_id']), 'recursive' => 2);
                    $all_template_data = $this->ProjectTemplate->find('first', $options);

                    $Easycase = ClassRegistry::init('Easycase');
                    $Easycase->recursive = -1;
                    $CaseActivity = ClassRegistry::init('CaseActivity');
                    $wrkflw_id = $this->Project->find('list', array('conditions' => array('Project.id' => $prjid), 'fields' => array('Project.workflow_id')));
                    $this->loadModel('Status');
                    $status_id = $this->Status->find('first', array('conditions' => array('Status.workflow_id' => $wrkflw_id), 'order' => 'Status.seq_order ASC'));
                    #echo "<pre>";print_r($status_id);exit;
                    $this->loadModel('ProjectUser');
                    $project_user_list = $this->ProjectUser->find('list', array('conditions' => array('ProjectUser.project_id' => $prjid), 'fields' => array('ProjectUser.user_id')));
                    $this->loadModel('Milestone');
                    $tmpDepends = array();
                    foreach ($all_template_data['ProjectTemplateMilestone'] as $key => $value) {
                        $milestone_case = array();
                        if (empty($value['is_default'])) {
                            $mlstn_data = array();
                            $milestone_uniq_id = $this->Format->generateUniqNumber();
                            $mlstn_data = array('uniq_id' => $milestone_uniq_id, 'project_id' => $prjid, 'user_id' => $ses_id, 'company_id' => $ses_comp, 'title' => $value['title'], 'description' => $value['description']);
                            if (!empty($value['start_date'])) {
                                $mlstn_data['start_date'] = $value['start_date'];
                            }
                            if (!empty($value['end_date'])) {
                                $mlstn_data['end_date'] = $value['end_date'];
                            }
                            $milestone_case[] = $mlstn_data;
                            $this->Milestone->saveAll($milestone_case);
                            $mlstId = $this->Milestone->getLastInsertID();
                        }
                        foreach ($value['ProjectTemplateCase'] as $k => $v) {
                            $postCases['Easycase']['uniq_id'] = $this->Format->generateUniqNumber();
                            $postCases['Easycase']['project_id'] = $prjid;
                            $postCases['Easycase']['user_id'] = $ses_id;
                            $postCases['Easycase']['type_id'] = 2;
                            $postCases['Easycase']['priority'] = 1;
                            $postCases['Easycase']['title'] = $v['title'];
                            $postCases['Easycase']['message'] = $v['description'];
                            $postCases['Easycase']['assign_to'] = !empty($v['assign_to']) && in_array($v['assign_to'], $project_user_list) ? $v['assign_to'] : 0;
                            if (defined('TLG') && TLG == 1) {
                                $estimated_hours = $v['estimated_hours'];
                                /* saving in secs */
                                if (strpos($estimated_hours, ':') > -1) {
                                    $split_est = explode(':', $estimated_hours);
                                    $est_sec = ((($split_est[0]) * 60) + intval($split_est[1])) * 60;
                                } else {
                                    $est_sec = $estimated_hours * 3600;
                                }
                                if (defined('TSG') && TSG == 1) {
                                    $status = ClassRegistry::init('Status');
                                    $crnt_status = $status->find('first', array('conditions' => array('id' => $status_id['Status']['id'])));
                                    $postCases['Easycase']['completed'] = $crnt_status['Status']['percentage'];
                                }
                                $estimated_hours = $est_sec;
                                $postCases['Easycase']['estimated_hours'] = $estimated_hours;
                            } else {
                                $postCases['Easycase']['estimated_hours'] = $v['estimated_hours'];
                            }
                            $postCases['Easycase']['due_date'] = $v['end_date'];
                            $postCases['Easycase']['gantt_start_date'] = $v['start_date'];
                            $postCases['Easycase']['depends'] = !empty($v['depends']) ? $v['depends'] : '';
                            $postCases['Easycase']['istype'] = 1;
                            $postCases['Easycase']['format'] = 2;
                            $postCases['Easycase']['status'] = 1;
                            $postCases['Easycase']['legend'] = $status_id['Status']['id'];
                            $postCases['Easycase']['isactive'] = 1;
                            $postCases['Easycase']['dt_created'] = GMT_DATETIME;
                            $postCases['Easycase']['actual_dt_created'] = GMT_DATETIME;
                            $caseNoArr = $Easycase->find('first', array('conditions' => array('Easycase.project_id' => $prjid), 'fields' => array('MAX(Easycase.case_no) as caseno')));
                            $caseNo = $caseNoArr[0]['caseno'] + 1;
                            $postCases['Easycase']['case_no'] = $caseNo;
                            if ($Easycase->saveAll($postCases)) {
                                $caseid = $Easycase->getLastInsertID();
                                if (defined('GTLG') && GTLG == 1) {
                                    if ($postCases['Easycase']['estimated_hours'] != '' && $postCases['Easycase']['gantt_start_date'] != '' && $postCases['Easycase']['assign_to'] != 0) {
                                        $isAssignedUserFree = $this->Postcase->setBookedData($postCases, $postCases['Easycase']['estimated_hours'], $caseid, $ses_comp);
                                        if ($isAssignedUserFree != 1) {
                                            $overloadDataArr = array(
                                                'assignTo' => $postCases['Easycase']['assign_to'],
                                                'caseId' => $caseid,
                                                'caseUniqId' => $postCases['Easycase']['uniq_id'],
                                                'est_hr' => $postCases['Easycase']['estimated_hours'] / 3600,
                                                'projectId' => $postCases['Easycase']['project_id'],
                                                'str_date' => $postCases['Easycase']['gantt_start_date']
                                            );
                                            $response = $this->Format->overloadUsers($overloadDataArr);
                                        }
                                    }
                                }
                                //if($v['depends']!= ''){                                
                                $tmpDepends[$v['id']][0] = $caseid;
                                $tmpDepends[$v['id']][1] = $v['depends'];
                                $tmpDepends[$v['id']][2] = $v['id'];
                                //}
                                if (empty($value['is_default'])) {
                                    $case_milestones[] = array('easycase_id' => $caseid, 'milestone_id' => $mlstId, 'project_id' => $prjid, 'user_id' => $ses_id);
                                }
                                $CaseActivity->recursive = -1;
                                $CaseAct['easycase_id'] = $caseid;
                                $CaseAct['user_id'] = $ses_id;
                                $CaseAct['project_id'] = $prjid;
                                $CaseAct['case_no'] = $caseNo;
                                $CaseAct['type'] = 1;
                                $CaseAct['dt_created'] = GMT_DATETIME;
                                $CaseActivity->saveAll($CaseAct);
                            }
                        }
                    }
                    //  print_r($tmpDepends);exit;
                    if (!empty($tmpDepends)) {
                        foreach ($tmpDepends as $k => $v) {
                            if (!empty($v[1])) {
                                $Easycase->id = $v[0];
                                $Easycase->saveField('depends', $tmpDepends[$v[1]][0]);
                            }
                        }
                    }
                    $this->loadModel('EasycaseMilestone');
                    $this->EasycaseMilestone->saveAll($case_milestones);
                }

                if ($emailarr != '') {
                    if (!empty($api_flag) && $api_flag == 1) {
                        $inviteduserlist = $this->Postcase->invitenewuser($emailarr, $prjid, $this, $createProject['Project']['api'], $ses_comp, $ses_id, $comp['Company']['uniq_id']);
                    } else {
                        $inviteduserlist = $this->Postcase->invitenewuser($emailarr, $prjid, $this, null, $ses_comp, $ses_id, $comp['Company']['uniq_id']);
                    }
                }
                if (!empty($api_flag) && $api_flag == 1) {
                    $d['status'] = "SUCCESS";
                    $d['message'] = strip_tags($postProject['Project']['name']) . " created successfully";
                    $d['name'] = strip_tags($postProject['Project']['name']);
                    $d['id'] = $prjid;
                    $d['uniq'] = $prjUniqId;
                    return json_encode($d);
                } else {
                    if ($this->request->is('ajax')) {
                        $d['status'] = "SUCCESS";
                        $d['message'] = "'" . strip_tags($postProject['Project']['name']) . "' created successfully";
                        $d['name'] = strip_tags($postProject['Project']['name']);
                        $d['id'] = $prjid;
                        $d['uniq'] = $prjUniqId;
                        echo json_encode($d);
                        exit;
                    }
                    if (!empty($createProject)) {
                        return $prjid;
                    }
                    $this->Session->write("SUCCESS", "'" . strip_tags($postProject['Project']['name']) . "' " . __("created successfully"));

                    setcookie('LAST_CREATED_PROJ', $prjid, time() + 3600, '/', DOMAIN_COOKIE, false, false);

                    $CompanyUser = ClassRegistry::init('CompanyUser');
                    $checkMem = $CompanyUser->find('all', array('conditions' => array('CompanyUser.company_id' => SES_COMP, 'CompanyUser.is_active' => 1)));
                    if (isset($checkMem['CompanyUser']['id']) && $checkMem['CompanyUser']['id']) {
//					$ProjectUser = ClassRegistry::init("ProjectUser");
//					$checkProjusr = $ProjectUser->find('first',array('conditions'=>array('ProjectUser.project_id'=>$prjid,'ProjectUser.user_id !='=>SES_ID)));
//					
//					if(isset($checkProjusr['ProjectUser']['id']) && $checkProjusr['ProjectUser']['id']) {
//						//setcookie('CREATE_CASE',1,time()+3600,'/',DOMAIN_COOKIE,false,false);
//						$this->redirect(HTTP_ROOT."dashboard");
//					}
//					else {
                        if (count($memberslist) < count($checkMem)) {
                            setcookie('LAST_PROJ', $prjid, time() + 3600, '/', DOMAIN_COOKIE, false, false);
                        }
                        setcookie('ASSIGN_USER', $prjid, time() + 3600, '/', DOMAIN_COOKIE, false, false);
                        setcookie('PROJ_NAME', trim($postProject['Project']['name']), time() + 3600, '/', DOMAIN_COOKIE, false, false);

                        if (empty($createProject)) {
                        $this->redirect(HTTP_ROOT . "projects/manage");
                        }
                    } else {
                        //setcookie('INVITE_USER',1,time()+3600,'/',DOMAIN_COOKIE,false,false);
                        //$this->redirect(HTTP_ROOT."dashboard");
                        if ($GLOBALS['project_count'] >= 1) {
                            if (count($memberslist) < count($checkMem)) {
                                setcookie('LAST_PROJ', $prjid, time() + 3600, '/', DOMAIN_COOKIE, false, false);
                            }
                            if (empty($createProject)) {
                            $this->redirect(HTTP_ROOT . "projects/manage/active-grid");
                            }
                        } else {
                            if (empty($createProject)) {
                            $this->redirect(HTTP_ROOT . 'onbording');
                        }
                    }
                }
                }

                //setcookie('NEW_PROJECT',$prjid,time()+3600,'/',DOMAIN_COOKIE,false,false);
            }
        } else {
            if (!empty($api_flag) && $api_flag == 1) {
                $d['status'] = "ERROR";
                $d['message'] = "Error creating project";
                return json_encode($d);
            } else if (!empty($createProject)) {
                return $prjid;
            } else if ($this->request->is('ajax')) {
                $d['status'] = "ERROR";
                $d['message'] = "Error creating project";
                return json_encode($d);
            } else {
                if ($this->request->is('ajax')) {
                    $d['status'] = "ERROR";
                    $d['message'] = "Error creating project";
                    echo json_encode($d);
                    exit;
                } else {
                    $this->Session->write("ERROR", __("Error creating project"));
                    $this->redirect(HTTP_ROOT . "projects/manage/");
                }
            }
        }
    }

    function check_proj_short_name() {
        $this->layout = 'ajax';
        ob_clean();
        if (isset($this->params['data']['shortname']) && trim($this->params['data']['shortname'])) {
            $count = $this->Project->find("count", array("conditions" => array('Project.short_name' => trim(strtoupper($this->params['data']['shortname'])), 'Project.company_id' => SES_COMP), 'fields' => 'DISTINCT Project.id'));
            $this->set('count', $count);
            $this->set('shortname', trim(strtoupper($this->params['data']['shortname'])));
        }
    }

    function assign() {
        if (isset($this->request->data['ProjectUser']['project_id'])) {





            $projectid = $this->request->data['ProjectUser']['project_id'];

            $lists1 = $this->request->data['ProjectUser']['mem_avl'] . ",";
            $lis1 = explode(",", $lists1);



            $lists2 = $this->request->data['ProjectUser']['mem_ext'];

            $lis2 = explode(",", $lists2);


            $lis1 = array_filter($lis1);
            $lis2 = array_filter($lis2);




            $ProjectUser = ClassRegistry::init('ProjectUser');
            $ProjectUser->recursive = -1;
            $getLastId = $ProjectUser->find('first', array('fields' => array('ProjectUser.id'), 'order' => array('ProjectUser.id' => 'DESC')));
            $lastid = $getLastId['ProjectUser']['id'];

            $query = "";
            $Easycase = ClassRegistry::init('Easycase');
            $Easycase->recursive = -1;
            $getcaseIds = $Easycase->find("all", array('conditions', array('Easycase.project_id' => $projectid, 'Easycase.istype' => 1), 'fields' => array('Easycase.id')));

            $CaseUserEmail = ClassRegistry::init('CaseUserEmail');
            $CaseUserEmail->recursive = -1;
            if (count($lis1)) {
                foreach ($lis1 as $ids1) {
                    $checkAvlMem1 = $ProjectUser->find('count', array('conditions' => array('ProjectUser.user_id' => $ids1, 'ProjectUser.project_id' => $projectid), 'fields' => 'DISTINCT ProjectUser.id'));
                    if ($checkAvlMem1) {
                        $ProjectUser->query("DELETE FROM project_users WHERE user_id=" . $ids1 . " AND project_id=" . $projectid);

                        if (count($getcaseIds)) {
                            foreach ($getcaseIds as $getid) {
                                if ($getid['Easycase']['id']) {
                                    $CaseUserEmail->query("UPDATE case_user_emails SET ismail='0' WHERE user_id=" . $ids1 . " AND easycase_id=" . $getid['Easycase']['id']);
                                }
                            }
                        }
                    }
                }
            }
            if (count($lis2)) {
                foreach ($lis2 as $ids2) {
                    $checkAvlMem2 = $ProjectUser->find('count', array('conditions' => array('ProjectUser.user_id' => $ids2, 'ProjectUser.project_id' => $projectid), 'fields' => 'DISTINCT id'));
                    if ($checkAvlMem2 == 0) {
                        $lastid++;
                        $ProjectUser->query("INSERT INTO project_users SET id='" . $lastid . "',user_id=" . $ids2 . ",project_id=" . $projectid . ",company_id='" . SES_COMP . "',dt_visited='" . GMT_DATETIME . "'");

                        if (count($getcaseIds)) {
                            foreach ($getcaseIds as $getid) {
                                if ($getid['Easycase']['id']) {
                                    $CaseUserEmail->query("UPDATE case_user_emails SET ismail='1' WHERE user_id=" . $ids2 . " AND easycase_id=" . $getid['Easycase']['id']);
                                }
                            }
                        }
                    }
                }
            }

            $prjid = $this->request->data['ProjectUser']['project_id'];
            $getProj = $this->Project->find('first', array('conditions' => array('Project.isactive' => 1, 'Project.id' => $prjid), 'fields' => array('Project.uniq_id', 'Project.name')));

            $this->Session->write("SUCCESS", __("User(s) successfully assigned to") . " '" . $getProj['Project']['name'] . "'");
            $this->redirect(HTTP_ROOT . "projects/assign/?pid=" . $getProj['Project']['uniq_id']);
        }

        $pid = NULL;
        $projId = NULL;
        $memsAvlArr = array();
        $custAvlArr = array();
        $memsExtArr = array();
        $custExtArr = array();
        $this->Project->recursive = -1;
        $projArr = $this->Project->find('all', array('conditions' => array('Project.isactive' => 1, 'Project.name !=' => '', 'Project.company_id' => SES_COMP), 'fields' => array('DISTINCT Project.uniq_id,Project.name')));

        if (isset($_GET['pid']) && $_GET['pid']) {
            $pid = $_GET['pid'];

            $getProj = $this->Project->find('first', array('conditions' => array('Project.isactive' => 1, 'Project.uniq_id' => $pid, 'Project.company_id' => SES_COMP), 'fields' => array('Project.id')));
            if (count($getProj['Project'])) {
                $projId = $getProj['Project']['id'];

                $ProjectUser = ClassRegistry::init('ProjectUser');
                //$ProjectUser->unbindModel(array('belongsTo' => array('Project')));

                if (SES_TYPE == 1) {
                    $memsAvlArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND User.isactive='1' AND User.name!='' AND NOT EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projId . ") ORDER BY User.istype ASC,User.name");

                    $memsExtArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser,project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND User.isactive='1' AND User.name!='' AND ProjectUser.project_id=" . $projId . " ORDER BY User.istype ASC,User.name");
                } else {
                    $memsAvlArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.user_type!='1' AND User.isactive='1' AND User.name!=''  AND NOT EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projId . ") ORDER BY User.istype ASC,User.name");



                    $memsExtArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser,project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND User.id = CompanyUser.user_id AND CompanyUser.user_type!='1' AND CompanyUser.company_id='" . SES_COMP . "' AND User.isactive='1' AND User.name!='' AND ProjectUser.project_id=" . $projId . " ORDER BY User.istype ASC,User.name");
                }
            }
        }
        $this->set('projArr', $projArr);
        $this->set('memsAvlArr', $memsAvlArr);
        //$this->set('custAvlArr',$custAvlArr);
        $this->set('memsExtArr', $memsExtArr);
        //$this->set('custExtArr',$custExtArr);
        $this->set('pid', $pid);
        $this->set('projId', $projId);
    }

    function gridview($projtype = NULL) {
        $page_limit = 15;
        $this->Project->recursive = -1;
        $pjid = NULL;
        if (isset($_GET['id']) && $_GET['id']) {
            $pjid = $_GET['id'];
        }
        if (isset($_GET['proj_srch']) && $_GET['proj_srch']) {
            $pjname = htmlentities(strip_tags($_GET['proj_srch']));
            $this->set('prjsrch', 'project search');
        }
        if (isset($_GET['page']) && $_GET['page']) {
            $page = $_GET['page'];
        }
        if (trim($pjid)) {
            $project = "Project";
            $getProj = $this->Project->find('first', array('conditions' => array('Project.id' => $pjid, 'Project.company_id' => SES_COMP), 'fields' => array('Project.name', 'Project.id')));
            if (isset($getProj['Project']['name']) && $getProj['Project']['name']) {
                $project = $getProj['Project']['name'];
            }
            if ($getProj['Project']['id']) {
                if (isset($_GET['action']) && $_GET['action'] == "activate") {
                    $this->Project->query("UPDATE projects SET isactive='1' WHERE id=" . $getProj['Project']['id']);
                    $this->Session->write("SUCCESS", "'" . $project . "' " . __("activated successfully"));
                    $redirect = HTTP_ROOT . "projects/manage/inactive/";
                    if (isset($_GET['pg']) && (intval($_GET['pg']) > 1)) {
                        $redirect = HTTP_ROOT . "projects/manage/inactive/?page=" . $_GET['pg'];
                    }
                    $this->redirect($redirect);
                }
                if (isset($_GET['action']) && $_GET['action'] == "delete") {
                    $this->Project->query("DELETE FROM projects WHERE id=" . $getProj['Project']['id']);

                    $ProjectUser = ClassRegistry::init('ProjectUser');
                    $ProjectUser->recursive = -1;
                    $ProjectUser->query("DELETE FROM project_users WHERE project_id=" . $getProj['Project']['id']);

                    $this->Session->write("SUCCESS", "'" . $project . "' " . __("deleted successfully"));
                    $this->redirect(HTTP_ROOT . "projects/gridview/");
                }
                if (isset($_GET['action']) && $_GET['action'] == "deactivate") {
                    $redirect = HTTP_ROOT . "projects/manage/";
                    if (isset($_GET['pg']) && (intval($_GET['pg']) > 1)) {
                        $redirect = HTTP_ROOT . "projects/manage/?page=" . $_GET['pg'];
                    }
                    $this->Project->query("UPDATE projects SET isactive='2' WHERE id=" . $getProj['Project']['id']);
                    $this->Session->write("SUCCESS", "'" . $project . "' " . __("deactivated successfully"));
                    $this->redirect($redirect);
                }
            } else {
                $this->Session->write("ERROR", __("Invalid or Wrong action!"));
                $this->redirect(HTTP_ROOT . "projects/gridview");
            }
        }

        $action = "";
        $uniqid = "";
        $query = "";
        if (isset($_GET['uniqid']) && $_GET['uniqid']) {
            $uniqid = $_GET['uniqid'];
        }
        if ($projtype == "disabled") {
            $query = "AND isactive='2'";
        } else {
            $query = "AND isactive='1'";
        }
        $query .= " AND company_id='" . SES_COMP . "'";
        if (isset($_GET['action']) && $_GET['action']) {
            $action = $_GET['action'];
        }
        $page = 1;
        $pageprev = 1;
        if (isset($_GET['page']) && $_GET['page']) {
            $page = $_GET['page'];
        }
        $limit1 = $page * $page_limit - $page_limit;
        $limit2 = $page_limit;

        $prjselect = $this->Project->query("SELECT name FROM projects AS Project WHERE name!='' " . $query . " ORDER BY name");
        $arrprj = array();
        foreach ($prjselect as $pjall) {
            if (isset($pjall['Project']['name']) && !empty($pjall['Project']['name'])) {
                array_push($arrprj, substr(trim($pjall['Project']['name']), 0, 1));
            }
        }
        if (isset($_GET['prj']) && $_GET['prj']) {
            //$_GET['prj'] = Sanitize::clean($_GET['prj'], array('encode' => false));
            $_GET['prj'] = chr($_GET['prj']);
            $pj = $_GET['prj'] . "%";
            $query .= " AND name LIKE '" . addslashes($pj) . "'";
        }

        if ($pjname) {
            $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS  id,uniq_id,name,user_id,project_type,short_name,isactive,(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,(select ROUND(SUM(easycases.hours), 1) as hours from easycases where easycases.project_id=Project.id and easycases.istype='2' and easycases.isactive='1') as totalhours,(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size  FROM case_files   WHERE case_files.project_id=Project.id) AS storage_used FROM projects AS Project WHERE name!='' " . $query . " and name LIKE '%" . addslashes($pjname) . "%' ORDER BY name LIMIT $limit1,$limit2 ");
        } else {
            $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS id,uniq_id,name,user_id,project_type,short_name,isactive,(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,(select ROUND(SUM(easycases.hours), 1) as hours from easycases where easycases.project_id=Project.id and easycases.istype='2' and easycases.isactive='1') as totalhours,(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size  FROM case_files   WHERE case_files.project_id=Project.id) AS storage_used FROM projects AS Project WHERE name!='' " . $query . " ORDER BY name LIMIT $limit1,$limit2");
        }

        $tot = $this->Project->query("SELECT FOUND_ROWS() as total");
        $CaseCount = $tot[0][0]['total'];
        $this->set('caseCount', $tot[0][0]['total']);

        $this->set(compact('data'));
        $this->set('total_records', $prjAllArr);
        $this->set('proj_srch', $pjname);
        $this->set('page_limit', $page_limit);
        $this->set('page', $page);
        $this->set('pageprev', $pageprev);
        $count_grid = count($prjAllArr);
        $this->set('count_grid', $count_grid);
        $this->set('prjAllArr', $prjAllArr);
        $this->set('projtype', $projtype);
        $this->set('action', $action);
        $this->set('uniqid', $uniqid);
        $this->set('arrprj', $arrprj);
        $this->set('page_limit', $page_limit);
        $this->set('casePage', $page);
    }

    function groupupdatealerts() {

        $this->loadModel('Project');
        $this->loadModel('ProjectUser');
        $project = $this->Project->getAllProjects();
        //$projectsForUser = $this->ProjectUser->getAllProjectsForUsers();
        $this->set('project', $project);
    }

    function projectMembers() {
        $this->layout = 'ajax';

        //Getting project id
        $this->loadModel('Project');
        $project = $this->Project->getProjectFields(array('Project.uniq_id' => $this->params['data']['id']), array('id'));

        //Getting project members of correspoding project
       // $this->loadModel('ProjectUser');
        $projectuser = $this->getProjectMembers($project['Project']['id']);

        //To whom sent an email
        $this->loadModel('DailyUpdate');
        $selecteduser = $this->DailyUpdate->getDailyUpdateFields($project['Project']['id']);

        $this->loadModel('TimezoneName');
        $timezones = $this->TimezoneName->find('all');
        $this->set('timezones', $timezones);

        $this->set('projectuser', $projectuser);
        $this->set('selecteduser', $selecteduser);
    }

    function dailyUpdate() {


        //Getting project id
        $this->loadModel('Project');
        $project = $this->Project->getProjectFields(array('Project.uniq_id' => $this->data['Project']['uniq_id']), array('id'));

        $usr = $this->data['Project']['user'];
        $this->loadModel('User');

        //Getting user ids
        $uids = '';
        foreach ($usr as $key => $value) {
            $user = $this->User->getUserFields(array('User.uniq_id' => $value), array('id'));
            $uids.="," . $user['User']['id'];
        }

        //Making an array to insert or update
        $data['company_id'] = SES_COMP;
        $data['project_id'] = $project['Project']['id'];
        $data['post_by'] = SES_ID;
        $data['user_id'] = ltrim($uids, ",");
        $data['timezone_id'] = $this->data['Project']['timezone_id'];
        $data['notification_time'] = trim($this->data['Project']['hour']) . ":" . trim($this->data['Project']['minute']);
        $data['days'] = $this->data['Project']['days'];

        $this->loadModel('DailyUpdate');
        //Check if insert or update
        $this->loadModel('DailyUpdate');
        $selecteduser = $this->DailyUpdate->getDailyUpdateFields($project['Project']['id']);
        if (isset($selecteduser['DailyUpdate']) && !empty($selecteduser['DailyUpdate'])) {
            $this->DailyUpdate->id = $selecteduser['DailyUpdate']['id'];
        }

        //Save or update records
        if ($this->DailyUpdate->save($data)) {

            $this->Session->write("SUCCESS", __("Group update alert has been saved successfully."));
        } else {

            $this->Session->write("ERROR", __("Failed to save of Group update alert."));
        }

        $this->redirect(HTTP_ROOT . "projects/groupupdatealerts");
    }

    function cancelDailyUpdate() {
        if (intval($this->params['pass'][0])) {
            $this->loadModel('DailyUpdate');
            if ($this->DailyUpdate->delete($this->params['pass'][0])) {

                $this->Session->write("SUCCESS", __("Group update alert has been saved successfully."));
            } else {

                $this->Session->write("ERROR", __("Failed to save of Group update alert."));
            }
        } else {

            $this->Session->write("ERROR", __("Failed to save of Group update alert."));
        }

        $this->redirect(HTTP_ROOT . "projects/groupupdatealerts");
    }

    function user_listing() {
        $this->layout = 'ajax';
        $projId = trim($this->params['data']['project_id']);
        if (isset($this->params['data']['userid']) && $this->params['data']['userid'] && isset($this->params['data']['InvitedUser']) && trim($this->params['data']['InvitedUser'])) {
            $UserInvitation = ClassRegistry::init('UserInvitation');
            $UserInvitation->unbindModel(array('belongsTo' => array('Project')));
            $checkAvlInvMem = $UserInvitation->query("SELECT * FROM `user_invitations` WHERE find_in_set('" . $projId . "', `user_invitations`.project_id) > 0 AND `user_invitations`.is_active = '1' AND `user_invitations`.user_id = '" . $this->params['data']['userid'] . "'");
            if ($checkAvlInvMem && !empty($checkAvlInvMem[0]['user_invitations']['project_id'])) {
                $pattern_array = array("/(,$projId,)/", "/(^$projId,)/", "/(,$projId$)/", "/(^$projId$)/");
                $replace_array = array(",", "", "", "");
                $mstr = preg_replace($pattern_array, $replace_array, $checkAvlInvMem[0]['user_invitations']['project_id']);
                $UserInvitation->query("UPDATE user_invitations SET project_id = '" . $mstr . "' where id = '" . $checkAvlInvMem[0]['user_invitations']['id'] . "'");
            }
            echo "updated";
            exit;
        }
        if (isset($this->params['data']['userid']) && $this->params['data']['userid']) {
            $uid = $this->params['data']['userid'];
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $ProjectUser->unbindModel(array('belongsTo' => array('Project')));
            $checkAvlMem3 = $ProjectUser->find('count', array('conditions' => array('ProjectUser.user_id' => $uid, 'ProjectUser.project_id' => $projId), 'fields' => 'DISTINCT ProjectUser.id'));
            if ($checkAvlMem3) {
                $ProjectUser->query("DELETE FROM project_users WHERE user_id=" . $uid . " AND project_id=" . $projId);
            }
            //Remove from Group update table , that user should not get mail when he is removed from a project.
            $this->loadModel('DailyUpdate');
            $DailyUpdate = $this->DailyUpdate->getDailyUpdateFields($projId, array('DailyUpdate.id', 'DailyUpdate.user_id'));
            if (isset($DailyUpdate) && !empty($DailyUpdate)) {
                $user_ids = explode(",", $DailyUpdate['DailyUpdate']['user_id']);
                if (($index = array_search($uid, $user_ids)) !== false) {
                    unset($user_ids[$index]);
                }
                $du['user_id'] = implode(",", $user_ids);
                $this->DailyUpdate->id = $DailyUpdate['DailyUpdate']['id'];
                $this->DailyUpdate->save($du);
            }
            echo "removed";
            exit;
        }

        $qry = '';
        if (isset($this->params['data']['name']) && trim($this->params['data']['name'])) {
            $name = trim($this->params['data']['name']);
            $qry = " AND User.name LIKE '%$name%'";
        }

        $ProjectUser = ClassRegistry::init('ProjectUser');
        $ProjectUser->unbindModel(array('belongsTo' => array('Project')));
        $memsArr = $ProjectUser->query("SELECT DISTINCT User.*,CompanyUser.*,ProjectUser.* FROM users AS User,company_users AS CompanyUser,project_users AS ProjectUser WHERE User.id=CompanyUser.user_id AND User.id=ProjectUser.user_id AND ProjectUser.project_id='" . $projId . "' AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active=1" . $qry . " ORDER BY User.name ASC");
        $memsExtArr['Member'] = $memsArr;

        $UserInvitation = ClassRegistry::init('UserInvitation');
        $memsUserInvArr = $UserInvitation->query("SELECT * FROM users AS User,user_invitations AS UserInvitation,company_users AS CompanyUser WHERE User.id=CompanyUser.user_id AND User.id=UserInvitation.user_id AND UserInvitation.company_id='" . SES_COMP . "' AND find_in_set('" . $projId . "', UserInvitation.project_id) > 0 AND UserInvitation.is_active = '1' AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active=2" . $qry . " ORDER BY User.name ASC");
        $memsExtArr['Invited'] = $memsUserInvArr;

        $CompanyUser = ClassRegistry::init('CompanyUser');
        $memsUserDisArr = $CompanyUser->query("SELECT DISTINCT User.*,CompanyUser.*,ProjectUser.* FROM users AS User,company_users AS CompanyUser,project_users AS ProjectUser WHERE User.id=CompanyUser.user_id AND User.id=ProjectUser.user_id AND ProjectUser.project_id='" . $projId . "' AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active=0" . $qry . " ORDER BY User.name ASC");
        $memsExtArr['Disabled'] = $memsUserDisArr;

        $this->set('memsExtArr', $memsExtArr);
        $this->set('pjid', $projId);
    }

    function add_user() {
        $this->layout = 'ajax';
        $projid = $this->params['data']['pjid'];
        $pjname = urldecode($this->params['data']['pjname']);
        $cntmng = $this->params['data']['cntmng'];
        $query = "";
        if (isset($this->params['data']['name']) && trim($this->params['data']['name'])) {
            $srchstr = addslashes($this->params['data']['name']);
            $query = "AND User.name LIKE '%$srchstr%'";
        }

        $ProjectUser = ClassRegistry::init('ProjectUser');

        $ProjectUser->unbindModel(array('belongsTo' => array('Project')));

        if (SES_TYPE == 1) {
            $memsNotExstArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' " . $query . " AND NOT EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projid . ") ORDER BY User.name");
            $memsExstArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' " . $query . " AND EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projid . ") ORDER BY User.name");
        } else {
            $memsNotExstArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' " . $query . " AND NOT EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projid . ") ORDER BY User.name");
            $memsExstArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' " . $query . " AND EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projid . ") ORDER BY User.name");
        }
        $this->set('pjname', $pjname);
        $this->set('projid', $projid);
        $this->set('memsNotExstArr', $memsNotExstArr);
        $this->set('memsExstArr', $memsExstArr);
        $this->set('cntmng', $cntmng);
    }

    function assign_userall() {
        $this->layout = 'ajax';
        $userid = $this->params['data']['userid'];
        $pjid = $this->params['data']['pjid'];

        $Company = ClassRegistry::init('Company');
        $comp = $Company->find('first', array('fields' => array('Company.name')));

        $ProjectUser = ClassRegistry::init('ProjectUser');
        $ProjectUser->recursive = -1;

        $getLastId = $ProjectUser->find('first', array('fields' => array('ProjectUser.id'), 'order' => array('ProjectUser.id' => 'DESC')));
        $lastid = $getLastId['ProjectUser']['id'];

        $Easycase = ClassRegistry::init('Easycase');
        $Easycase->recursive = -1;

        $CaseUserEmail = ClassRegistry::init('CaseUserEmail');
        $CaseUserEmail->recursive = -1;

        //$getcaseIds = $Easycase->find("all",array('conditions', array('Easycase.project_id' => $pjid, 'Easycase.istype' => 1), 'fields' => array('Easycase.id')));
        if (count($userid)) {
            foreach ($userid as $id) {
                $checkAvlMem2 = $ProjectUser->find('count', array('conditions' => array('ProjectUser.user_id' => $id, 'ProjectUser.project_id' => $pjid, 'ProjectUser.company_id' => SES_COMP), 'fields' => 'DISTINCT id'));
                if ($checkAvlMem2 == 0) {
                    $lastid++;
                    if (defined('ROLE') && ROLE == 1) {
                        $CompanyUser = ClassRegistry::init('CompanyUser');
                        $roleAction = ClassRegistry::init('RoleAction');
                        $projectAction = ClassRegistry::init('ProjectAction');
                        $roleId = $CompanyUser->find('first', array('conditions' => array('CompanyUser.company_id' => SES_COMP, 'CompanyUser.user_id' => $id), 'fields' => 'CompanyUser.role_id'));
                        $ProjectUser->query("INSERT INTO project_users SET id='" . $lastid . "',user_id=" . $id . ",project_id=" . $pjid . ",company_id='" . SES_COMP . "',role_id='" . $roleId['CompanyUser']['role_id'] . "',dt_visited='" . GMT_DATETIME . "'");
                        $actions = $roleAction->find('all', array('conditions' => array('RoleAction.company_id' => SES_COMP, 'RoleAction.role_id' => $roleId['CompanyUser']['role_id']), 'fields' => array('RoleAction.role_id', 'RoleAction.action_id', 'RoleAction.is_allowed')));
                        if (!empty($actions)) {
                            foreach ($actions as $k => $action) {
                                $action['ProjectAction'] = $action['RoleAction'];
                                $action['ProjectAction']['project_id'] = $pjid;
                                unset($action['RoleAction']);
                                $projectAction->saveAll($action);
                            }
                        }
                    } else {
                        $ProjectUser->query("INSERT INTO project_users SET id='" . $lastid . "',user_id=" . $id . ",project_id=" . $pjid . ",company_id=" . SES_COMP . ",dt_visited='" . GMT_DATETIME . "'");
                    }

                    /* if(count($getcaseIds))
                      {
                      foreach($getcaseIds as $getid)
                      {
                      if($getid['Easycase']['id']) {
                      $CaseUserEmail->query("UPDATE case_user_emails SET ismail='1' WHERE user_id=".$id." AND easycase_id=".$getid['Easycase']['id']);
                      }
                      }
                      } */
                }
            }
        }
        if (count($userid)) {
            $Company = ClassRegistry::init('Company');
            $comp = $Company->find('first', array('fields' => array('Company.name')));
            foreach ($userid as $id) {
                $this->generateMsgAndSendPjMail($pjid, $id, $comp);
            }
        }
        echo "success";
        exit;
    }

    function add_template() {
        //pr($this->request);exit;
        if (isset($this->request->data['ProjectTemplateCase']) && !empty($this->request->data['ProjectTemplateCase'])) {
            if (isset($this->request->data['submit_template']) && count($this->request->data['ProjectTemplateCase']['title'])) {
                $this->loadModel('ProjectTemplateCase');
                $arr = $this->request->data['ProjectTemplateCase']['title'];
                $count_arr = 0;
                foreach ($arr as $cs) {
                    if (isset($cs) && !empty($cs)) {
                        $temp_case['user_id'] = SES_ID;
                        $temp_case['company_id'] = SES_COMP;
                        $temp_case['template_id'] = $this->request->data['ProjectTemplateCase']['template_id'];
                        $temp_case['title'] = $cs;
                        $temp_case['description'] = $this->request->data['ProjectTemplateCase']['description'][$count_arr];
                        $this->ProjectTemplateCase->saveAll($temp_case);
                    }
                    $count_arr++;
                }
            }
            $this->Session->write("SUCCESS", __("Template tasks added successfully"));
            $this->redirect(HTTP_ROOT . "projects/manage_template/");
        }
        $this->loadModel('ProjectTemplate');
        $prj = $this->ProjectTemplate->find('all', array('conditions' => array('ProjectTemplate.company_id' => SES_COMP, 'ProjectTemplate.is_active' => 1), 'fields' => array('ProjectTemplate.id', 'ProjectTemplate.module_name')));
        $this->set('template_mod', $prj);
    }

    function manage_template() {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $this->loadModel("ProjectTemplate");
            $this->ProjectTemplate->id = $_GET['id'];
            $this->ProjectTemplate->delete();
            ClassRegistry::init('ProjectTemplateCase')->query("Delete FROM project_template_cases WHERE template_id='" . $_GET['id'] . "'");
            $this->Session->write("SUCCESS", __("Template Deleted successfully"));
            $this->redirect(HTTP_ROOT . "projects/manage_template/");
        } else if (isset($this->request->query['act']) && $this->request->query['act']) {
            $v = urldecode(trim($this->request->query['act']));
            $this->loadModel("ProjectTemplate");
            $this->ProjectTemplate->id = $v;
            if ($this->ProjectTemplate->saveField("is_active", 1)) {
                $this->Session->write("SUCCESS", __("Template activated successfully"));
                $this->redirect(HTTP_ROOT . "projects/manage_template/");
            } else {
                $this->Session->write("ERROR", __("Template can't be activated.Please try again."));
                $this->redirect(HTTP_ROOT . "projects/manage_template/");
            }
        } else if (isset($this->request->query['inact']) && $this->request->query['inact']) {
            $v = urldecode(trim($this->request->query['inact']));
            $this->loadModel("ProjectTemplate");
            $this->ProjectTemplate->id = $v;
            if ($this->ProjectTemplate->saveField("is_active", 0)) {
                $this->Session->write("SUCCESS", __("Template deactivated successfully"));
                $this->redirect(HTTP_ROOT . "projects/manage_template/");
            } else {
                $this->Session->write("ERROR", __("Template can't be deactivated.Please try again."));
                $this->redirect(HTTP_ROOT . "projects/manage_template/");
            }
        }
        $proj_temp = ClassRegistry::init('ProjectTemplate')->find('all', array('conditions' => array('ProjectTemplate.company_id' => SES_COMP)));
        $proj_temp_active = ClassRegistry::init('ProjectTemplate')->find('all', array('conditions' => array('ProjectTemplate.company_id' => SES_COMP, 'ProjectTemplate.is_active' => 1)));
        $this->set('proj_temp', $proj_temp);
        $this->set('proj_temp_active', $proj_temp_active);
    }

    function ajax_add_template_module() {
        //print_r($this->params['data']['title']);exit;
        $this->layout = 'ajax';
        $title = $this->params['data']['title'];
        if (isset($this->params['data']['title']) && !empty($this->params['data']['title'])) {
            $this->loadModel('ProjectTemplate');
            $prj = $this->ProjectTemplate->find('count', array('conditions' => array('ProjectTemplate.module_name' => $this->params['data']['title'], 'ProjectTemplate.company_id' => SES_COMP)));
            if ($prj == 0) {
                $this->request->data['ProjectTemplate']['user_id'] = SES_ID;
                $this->request->data['ProjectTemplate']['company_id'] = SES_COMP;
                $this->request->data['ProjectTemplate']['module_name'] = $this->params['data']['title'];
                $this->request->data['ProjectTemplate']['is_default'] = 1;
                $this->request->data['ProjectTemplate']['is_active'] = 1;
                if ($this->ProjectTemplate->save($this->request->data)) {
                    $last_insert_id = $this->ProjectTemplate->getLastInsertId();
                    echo $title . "-" . $last_insert_id;
                } else {
                    echo "0";
                }
            } else {
                echo "0";
            }
        }
        exit;
    }

    function ajax_add_template_cases() {
        $this->layout = 'ajax';
        ob_clean();
        if (isset($this->params['data']['pj_id']) && isset($this->params['data']['temp_mod_id'])) {
            $this->loadModel('TemplateModuleCase');
            $prj = $this->TemplateModuleCase->find('count', array('conditions' => array('TemplateModuleCase.company_id' => SES_COMP, 'TemplateModuleCase.project_id' => $this->params['data']['pj_id'])));
            if ($prj == 0) {
                $this->request->data['TemplateModuleCase']['template_module_id'] = $this->params['data']['temp_mod_id'];
                $this->request->data['TemplateModuleCase']['user_id'] = SES_ID;
                $this->request->data['TemplateModuleCase']['company_id'] = SES_COMP;
                $this->request->data['TemplateModuleCase']['project_id'] = $this->params['data']['pj_id'];
                if ($this->TemplateModuleCase->save($this->request->data)) {
                    $this->loadModel("ProjectTemplateCase");
                    $pjtemp = $this->ProjectTemplateCase->find('all', array('conditions' => array('ProjectTemplateCase.template_id' => $this->params['data']['temp_mod_id'], 'ProjectTemplateCase.company_id' => SES_COMP)));
                    $Easycase = ClassRegistry::init('Easycase');
                    $Easycase->recursive = -1;
                    $CaseActivity = ClassRegistry::init('CaseActivity');
                    foreach ($pjtemp as $temp) {
                        $postCases['Easycase']['uniq_id'] = md5(uniqid());
                        $postCases['Easycase']['project_id'] = $this->params['data']['pj_id'];
                        $postCases['Easycase']['user_id'] = SES_ID;
                        $postCases['Easycase']['type_id'] = 2;
                        $postCases['Easycase']['priority'] = 1;
                        $postCases['Easycase']['title'] = $temp['ProjectTemplateCase']['title'];
                        $postCases['Easycase']['message'] = $temp['ProjectTemplateCase']['description'];
                        $postCases['Easycase']['assign_to'] = SES_ID;
                        $postCases['Easycase']['due_date'] = "";
                        $postCases['Easycase']['istype'] = 1;
                        $postCases['Easycase']['format'] = 2;
                        $postCases['Easycase']['status'] = 1;
                        $postCases['Easycase']['legend'] = 1;
                        $postCases['Easycase']['isactive'] = 1;
                        $postCases['Easycase']['dt_created'] = GMT_DATETIME;
                        $postCases['Easycase']['actual_dt_created'] = GMT_DATETIME;
                        $caseNoArr = $Easycase->find('first', array('conditions' => array('Easycase.project_id' => $this->params['data']['pj_id']), 'fields' => array('MAX(Easycase.case_no) as caseno')));
                        $caseNo = $caseNoArr[0]['caseno'] + 1;
                        $postCases['Easycase']['case_no'] = $caseNo;
                        if ($Easycase->saveAll($postCases)) {
                            $caseid = $Easycase->getLastInsertID();
                            $CaseActivity->recursive = -1;
                            $CaseAct['easycase_id'] = $caseid;
                            $CaseAct['user_id'] = SES_ID;
                            $CaseAct['project_id'] = $this->params['data']['pj_id'];
                            $CaseAct['case_no'] = $caseNo;
                            $CaseAct['type'] = 1;
                            $CaseAct['dt_created'] = GMT_DATETIME;
                            $CaseActivity->saveAll($CaseAct);
                        }
                    }echo "1";
                    exit;
                }
            } else {
                echo "0";
                exit;
            }
        }
        exit;
    }

    function ajax_view_template_cases() {
        $this->layout = 'ajax';
        $this->loadModel("ProjectTemplateCase");
        //$pjtemp = $this->ProjectTemplate->find('all', array('conditions'=> array('ProjectTemplate.template_id'=>$this->params['data']['temp_id'],'ProjectTemplate.company_id'=>SES_COMP)));
        $pjtemp = $this->ProjectTemplateCase->find('all', array('conditions' => array('ProjectTemplateCase.template_id' => $this->params['data']['temp_id'], 'ProjectTemplateCase.company_id' => SES_COMP)));
        $this->set('temp_dtls_cases', $pjtemp);
    }

    function ajax_refresh_template_module() {
        $this->layout = 'ajax';
        $this->loadModel('ProjectTemplate');
        $prj = $this->ProjectTemplate->find('all', array('conditions' => array('ProjectTemplate.company_id' => SES_COMP, 'ProjectTemplate.is_active' => 1), 'fields' => array('ProjectTemplate.id', 'ProjectTemplate.module_name')));
        $this->set('template_mod', $prj);
        $this->set('tmp_id', $this->params['data']['tmp_id']);
    }

    function ajax_view_temp_cases() {
        $this->layout = 'ajax';
        $pjtemp = ClassRegistry::init('ProjectTemplateCase')->find('all', array('conditions' => array('ProjectTemplateCase.template_id' => $this->params['data']['template_id']), 'fields' => array('ProjectTemplateCase.title', 'ProjectTemplateCase.description', 'ProjectTemplateCase.created')));
        $this->loadModel('ProjectTemplate');
        $tmpmod = ClassRegistry::init('ProjectTemplate')->find('first', array('conditions' => array('ProjectTemplate.id' => $this->params['data']['template_id']), 'fields' => array('ProjectTemplate.module_name')));
        $this->set('mod_name', $tmpmod['ProjectTemplate']['module_name']);
        $this->set('temp_dtls_cases', $pjtemp);
    }

    function ajax_new_project() {
        $this->layout = 'ajax';
        if (defined('PT') && PT == 1) {
            $this->loadModel('ProjectTemplate.TemplateModule');
            $modlist = ClassRegistry::init('ProjectTemplate.ProjectTemplate')->find('all', array('conditions' => array('ProjectTemplate.company_id' => SES_COMP), 'fields' => array('ProjectTemplate.module_name', 'ProjectTemplate.id'), 'order' => 'ProjectTemplate.created DESC'));
            $this->set("templates_modules", $modlist);
        }

        $this->loadModel('User');
        $userArr = $this->User->query("SELECT User.name,User.last_name,User.id,User.short_name,CompanyUser.user_type FROM users AS User,company_users AS CompanyUser WHERE User.id=CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active ='1' AND CompanyUser.user_type!='3' AND User.isactive='1' ORDER BY CompanyUser.user_type ASC");
        $this->set("userArr", $userArr);
    }

    function ajax_json_members() {
        $this->layout = 'ajax';
        $search = $this->params->query['tag'];

        $this->loadModel('User');

        $userArr = $this->User->query("SELECT User.name,User.last_name,User.id,User.short_name,User.email FROM users AS User,company_users AS CompanyUser WHERE User.id=CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND CompanyUser.user_type='3' AND User.isactive='1' AND (User.name LIKE '%" . $search . "%' OR User.email LIKE '%" . $search . "%') ORDER BY User.name ASC");


        ob_clean();
        $items = array();
        foreach ($userArr as $urs) {
            //$unm = $urs['User']['name']." &lt".$urs['User']['email']."&gt;";
            $unm = $urs['User']['name'] . '|' . $urs['User']['email'];
            $items[] = array("name" => $unm, "value" => $urs['User']['id']);
        }
        print json_encode($items);
        exit;
    }

    function ajax_json_project() {
        $this->layout = 'ajax';
        $search = isset($this->params->query['q']) ? $this->params->query['q'] : $this->params->query['tag'];
        $this->loadModel('ProjectUser');
        //$proj_array = $this->ProjectUser->query("SELECT project_users.project_id FROM project_users WHERE project_users.user_id = '".SES_ID."' AND project_users.company_id = '".SES_COMP."'");
        $proj_array = $this->ProjectUser->query("SELECT project_users.project_id FROM project_users WHERE project_users.user_id = '" . SES_ID . "' AND project_users.project_id NOT IN(" . $this->params['pass'][0] . ")");
        $projcts = array();
        foreach ($proj_array as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $projcts[] = $v1['project_id'];
            }
        }
        $this->Project->recursive = -1;
        $projname_array = $this->Project->find('all', array('conditions' => array('AND' => array('Project.id' => $projcts, 'Project.name LIKE "%' . $search . '%"')), 'fields' => array('Project.id', 'Project.name'), 'order' => 'Project.name asc'));
        ob_clean();
        $items = array();

        foreach ($projname_array as $urs) {
            $items[] = array("id" => $urs['Project']['id'], "name" => $urs['Project']['name']);
        }
        print json_encode($items);
        exit;
    }

    function ajax_template_case_listing() {
        $this->layout = 'ajax';
        //$all_cases=ClassRegistry::init('ProjectTemplateCase')->find('all',array('conditions'=>array('ProjectTemplateCase.template_id'=>$this->params['data']['template_id'],'ProjectTemplateCase.company_id'=> SES_COMP)));
        if (isset($this->params['data']['rem_template_id']) && $this->params['data']['rem_template_id']) {
            $this->loadModel("ProjectTemplateCase");
            $this->ProjectTemplateCase->id = $this->params['data']['rem_template_id'];
            $this->ProjectTemplateCase->delete();
            echo "removed";
            exit;
        }
        $all_cases = ClassRegistry::init('ProjectTemplateCase')->query("SELECT User.short_name,User.name,ProjectTemplateCase.*  FROM users AS User,project_template_cases AS ProjectTemplateCase WHERE ProjectTemplateCase.template_id='" . $this->params['data']['template_id'] . "' AND ProjectTemplateCase.company_id='" . SES_COMP . "' AND ProjectTemplateCase.user_id=User.id ;");
        $this->set("templates_cases", $all_cases);
    }

    function ajax_template_edit() {
        $this->layout = 'ajax';
        ob_clean();
        if (isset($this->params['data']['template_id']) && $this->params['data']['template_id'] && isset($this->params['data']['count']) && $this->params['data']['count']) {
            $temp_id = $this->params['data']['template_id'];
            $cnt = $this->params['data']['count'];
            $ttl = urldecode($this->params['data']['module_name']);
            $res = ClassRegistry::init('ProjectTemplate')->find('all', array('conditions' => array('module_name' => $ttl, 'company_id' => SES_COMP)));
            if (count($res) == 0) {
                $this->loadModel("ProjectTemplate");
                $this->ProjectTemplate->id = $temp_id;
                if ($this->ProjectTemplate->saveField("module_name", $ttl)) {
                    echo "<a class='classhover' href='javascript:void(0);'  title='Click here to view tasks' onclick='opencases($cnt);caseListing($cnt,$temp_id)'>$ttl</a>";
                    exit;
                } else {
                    echo "fail";
                    exit;
                }
            } else {
                echo "exist";
                exit;
            }
        } else {
            echo "fail";
            exit;
        }
    }

    function assign_template_project() {
        $this->loadModel("ProjectTemplate");
        $res = $this->ProjectTemplate->find('all', array('conditions' => array('ProjectTemplate.module_name !=' => '', 'ProjectTemplate.company_id' => SES_COMP, 'ProjectTemplate.is_active' => 1)));
        $this->set('temp_module', $res);
        $this->Project->recursive = -1;
        $project_details = $this->Project->find('all', array('conditions' => array('Project.company_id' => SES_COMP, 'Project.isactive' => 1), 'fields' => array('Project.name', 'Project.id')));
        $this->set('project_details', $project_details);
    }

    function update_email_notification() {
        $this->layout = 'ajax';
        $proj_user_id = $this->params['data']['projectuser_id'];
        $email_type = $this->params['data']['type'];
        if ($proj_user_id && $email_type) {
            if ($email_type == 'off') {
                $this->loadModel('ProjectUser');
                $this->ProjectUser->query("UPDATE project_users SET default_email=0 where id='" . $proj_user_id . "'");
            } else {
                $this->loadModel('ProjectUser');
                $this->ProjectUser->query("UPDATE project_users SET default_email=1 where id='" . $proj_user_id . "'");
            }
        }
        echo "sucess";
        exit;
    }

    function ajax_save_filter() {
        $this->layout = 'ajax';
        //For Case Status
        if (isset($this->params['data']['caseStatus']) && $this->params['data']['caseStatus']) {
            $case_status = $this->params['data']['caseStatus'];
        } elseif ($_COOKIE['STATUS']) {
            $case_status = $_COOKIE['STATUS'];
        }

        if ($case_status && $case_status != "all") {
            $case_status = strrev($case_status);
            if (strstr($case_status, "-")) {
                $expst = explode("-", $case_status);
                foreach ($expst as $st) {
                    $status.= $this->Format->displayStatus($st) . ", ";
                }
            } else {
                $status = $this->Format->displayStatus($case_status) . ", ";
            }
            $arr['case_status'] = trim($status, ', ');
            //$val =1;
        } else {
            $arr['case_status'] = 'All';
        }

        //For case types
        if (isset($this->params['data']['caseType']) && $this->params['data']['caseType']) {
            $case_types = $this->params['data']['caseType'];
        } elseif ($_COOKIE['CS_TYPES']) {
            $case_types = $_COOKIE['CS_TYPES'];
        }
        $types = '';
        if ($case_types && $case_types != "all") {
            $case_types = strrev($case_types);
            if (strstr($case_types, "-")) {
                $expst3 = explode("-", $case_types);
                foreach ($expst3 as $st3) {
                    $types.= $this->Format->caseBcTypes($st3) . ", ";
                }
                $types = trim($types, ', ');
            } else {
                $types = $this->Format->caseBcTypes($case_types);
            }
            $arr['case_types'] = $types;
            //$val =1;
        } else {
            $arr['case_types'] = 'All';
        }
        //For Priority
        if (isset($this->params['data']['casePriority']) && $this->params['data']['casePriority']) {
            $pri_fil = $this->params['data']['casePriority'];
        } elseif ($_COOKIE['PRIORITY']) {
            $pri_fil = $_COOKIE['PRIORITY'];
        }
        if ($pri_fil && $pri_fil != "all") {
            if (strstr($pri_fil, "-")) {
                $expst2 = explode("-", $pri_fil);
                foreach ($expst2 as $st2) {
                    $pri.= $st2 . ", ";
                }
                $pri = trim($pri, ', ');
            } else {
                $pri = $pri_fil;
            }
            $arr['pri'] = $pri;
            //$val =1;
        } else {
            $arr['pri'] = 'All';
        }
        //For Case Members 
        if (isset($this->params['data']['caseMemeber']) && $this->params['data']['caseMemeber']) {
            $case_member = $this->params['data']['caseMemeber'];
        } elseif ($_COOKIE['MEMBERS']) {
            $case_member = $_COOKIE['MEMBERS'];
        }
        if ($case_member && $case_member != "all") {
            if (strstr($case_member, "-")) {
                $expst4 = explode("-", $case_member);
                foreach ($expst4 as $st4) {
                    $mems .= $this->Format->caseBcMems($st4) . ", ";
                }
            } else {
                $mems = $this->Format->caseBcMems($case_member) . ", ";
            }
            $arr['case_member'] = trim($mems, ', ');
            //$val =1;
        } else {
            $arr['case_member'] = 'All';
        }


        //For Case Date Status .... 
        if (isset($this->params['data']['caseDate']) && $this->params['data']['caseDate']) {
            $date = $this->params['data']['caseDate'];
        } else {

            $date = $this->Cookie->read('DATE');
        }
        if (!empty($date)) {
            //$val = 1;
            if (trim($date) == 'one') {
                $arr['date'] = "Past hour";
            } else if (trim($date) == '24') {
                $arr['date'] = "Past 24Hour";
            } else if (trim($date) == 'week') {
                $arr['date'] = "Past Week";
            } else if (trim($date) == 'month') {
                $arr['date'] = "Past month";
            } else if (trim($date) == 'year') {
                $arr['date'] = "Past Year";
            } else if (strstr(trim($date), ":")) {
                $arr['date'] = str_replace(":", " - ", $date);
            }
        } else {
            $arr['date'] = "Any Time";
        }
        $this->set('memebers', $arr['case_member']);
        $this->set('priority', $arr['pri']);
        $this->set('type', $arr['case_types']);
        $this->set('status', $arr['case_status']);
        $this->set('date', $arr['date']);

        $this->set('memebers_val', $case_member);
        $this->set('priority_val', $pri_fil);
        $this->set('type_val', $case_types);
        $this->set('status_val', $case_status);
        $this->set('date_val', $date);
    }

    function ajax_customfilter_save() {
        $this->layout = 'ajax';

        $caseStatus = $this->params['data']['caseStatus'];
        $caseType = $this->params['data']['caseType'];
        $caseDate = $this->params['data']['caseDate'];
        $caseMemeber = $this->params['data']['caseMemeber'];
        $casePriority = $this->params['data']['casePriority'];
        $filterName = $this->params['data']['filterName'];
        $projuniqid = $this->params['data']['projuniqid'];
        $this->loadModel('CustomFilter');
        $this->CustomFilter->query("INSERT INTO custom_filters SET project_uniq_id='" . $projuniqid . "', company_id='" . SES_COMP . "', user_id='" . SES_ID . "', filter_name='" . $filterName . "',filter_date='" . $caseDate . "', filter_type_id='" . $caseType . "',filter_status='" . $caseStatus . "', filter_member_id='" . $caseMemeber . "', filter_priority='" . $casePriority . "', dt_created='" . GMT_DATETIME . "'");

        echo "success";
        exit;
    }

    function ajax_custom_filter_show() {
        $this->layout = 'ajax';
        $limit_1 = $this->params['data']['limit1'];
        if (isset($limit_1)) {
            $limit1 = (int) $limit_1 + 3;
            $limit2 = 3;
        } else {
            $limit1 = 0;
            $limit2 = 3;
        }
        $this->loadModel('CustomFilter');
        $getcustomfilter = "SELECT SQL_CALC_FOUND_ROWS * FROM custom_filters AS CustomFilter WHERE CustomFilter.company_id = '" . SES_COMP . "' and CustomFilter.user_id =  '" . SES_ID . "' ORDER BY CustomFilter.dt_created DESC LIMIT $limit1,$limit2";
        $getfilter = $this->CustomFilter->query($getcustomfilter);
        $tot = $this->CustomFilter->query("SELECT FOUND_ROWS() as total");
        //echo '<pre>';print_r($tot);
        $this->set('getfilter', $getfilter);
        $this->set('limit1', $limit1);
        $this->set('totalfilter', $tot[0][0]['total']);
    }

    /**
     * @method public importexport(int proj_id) Dataimport Interface 
     */
    function importexport($proj_id = '', $radio = '', $page = '', $all = '', $pname = '', $srch = '') {

        if (!$proj_id && (!isset($GLOBALS['getallproj'][0]['Project']['uniq_id']) && $GLOBALS['getallproj'][0]['Project']['uniq_id'])) {
            $this->redirect(HTTP_ROOT . 'projects/manage/');
            exit;
        } else {
            if (!empty($proj_id) && $proj_id == 'all') {
                $this->set('upload_file', 1);
                $this->set('proj_id', $proj_id);
                $this->set('proj_uid', $radio);
                $this->set('import_pjname', $proj_id);
            } else {
                if (!$proj_id)
                    $proj_id = $GLOBALS['getallproj'][0]['Project']['uniq_id'];
                $this->Project->recursive = -1;
                $proj_details = $this->Project->find('first', array('conditions' => array('uniq_id' => $proj_id, 'company_id' => SES_COMP)));
                if ($proj_details && (SES_TYPE <= 2)) {
                    $this->set('upload_file', 1);
                    $this->set('proj_id', $proj_details['Project']['id']);
                    $this->set('proj_uid', $proj_id);
                    $this->set('import_pjname', $proj_details['Project']['name']);
                } else {
                    $this->redirect(HTTP_ROOT . 'projects/gridview/');
                    exit;
                }
            }
        }
    }

    /**
     * @method public data_import Dataimport Interface 
     */
    /* function csv_dataimport() {
      $project_id = $this->data['proj_id'];
      $project_uid = $this->data['proj_uid'];
      $task_type_arr = array('enhancement', 'enh', 'bug', 'research n do', 'rnd', 'quality assurance', 'qa', 'unit testing', 'unt', 'maintenance', 'mnt', 'others', 'oth', 'release', 'rel', 'update', 'upd', 'development', 'dev');
      //  $task_status_arr = array('new', 'close', 'wip', 'resolve', 'resolved', 'closed');
      $task_status_arr = array();
      $this->loadModel('User');
      $this->loadModel('ProjectUser');
      $this->loadModel('Project');
      $this->Project->recursive = -1 ;

      // to get list of statuses present in the selected projects
      $status_name =$this->Project->query("SELECT status.name AS Name from statuses as status LEFT JOIN projects as project ON status.workflow_id = project.workflow_id WHERE project.id =".$project_id." ORDER BY status.seq_order ASC");
      foreach($status_name as $s => $sn){
      $task_status_arr[] = strtolower($sn['status']['Name']);
      }

      $task_assign_to_userid = $this->ProjectUser->find('list', array('conditions' => array('company_id' => SES_COMP, 'project_id' => $project_id), 'fields' => 'user_id'));
      $task_assign_to_users = $this->User->find('list', array('conditions' => array('id' => $task_assign_to_userid, 'isactive' => 1), 'fields' => 'email'));

      //$fields_arr = array('milestone title','milestone description','start date','end date','title','description','due date','status','type','assigned to');
      $fields_arr = array('title', 'description', 'due date', 'status', 'type', 'assigned to');

      if (isset($_FILES['import_csv'])) {
      //$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream');
      $ext = pathinfo($_FILES['import_csv']['name'], PATHINFO_EXTENSION);
      //if(in_array($_FILES['import_csv']['type'],$mimes)){
      if (strtolower($ext) == 'csv') {
      $csv_info = $_FILES['import_csv'];
      //Uploading the csv file to Our server
      $file_name = SES_ID . "_" . $project_id . "_" . $csv_info['name'];
      @copy($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);

      $row = 1;
      // Counting total rows and Restricting from uploading a file having more then 1000 record
      $linecount = count(file(CSV_PATH . "task_milstone/" . $file_name));
      if ($linecount > 1001) {
      @unlink($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
      $this->Session->write("ERROR", "Please split the file and upload again. Your file contain more than 1000 rows");
      $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
      exit;
      }
      if ($csv_info['size'] > 2097152) {
      @unlink($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
      $this->Session->write("ERROR", "Please upload a file with size less then 2MB");
      $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
      exit;
      }
      //Parsing the csv file
      if (($handle = fopen(CSV_PATH . "task_milstone/" . $file_name, "r")) !== FALSE) {
      $i = 0;
      $j = 0;
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      if (!$i) {
      // Check for column count
      if (count($data) >= 1) {
      // Check for exact number of fields
      foreach ($data AS $key => $val) {
      if (!in_array(strtolower($val), $fields_arr)) {
      @unlink($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
      $this->Session->write("ERROR", __("Invalid CSV file").", <a href='" . HTTP_ROOT . "projects/download_sample_csvfile' style='text-decoration:underline;color:#0000FF'>".__("Download")."</a> ".__("and check with our sample file"));
      $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
      exit;
      }
      }
      $fileds = $data;
      //$header_arr = array_flip($data);
      foreach ($data AS $key => $val) {
      $header_arr[strtolower($val)] = $key;
      }
      } else {
      @unlink($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
      $this->Session->write("ERROR", __("Require atleast Task Title column to import the Tasks"));
      $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
      exit;
      }
      } else {

      // Verifing data
      $value = $data;
      //					if($value[$header_arr['title']]){
      //						$mtitle = $value[$header_arr['milestone title']];
      ////						$milestone_arr[$value[$header_arr['milestone title']]]['title'] = $value[$header_arr['milestone title']];
      ////						$milestone_arr[$value[$header_arr['milestone title']]]['desc'] = $value[$header_arr['milestone description']];
      ////						$milestone_arr[$value[$header_arr['milestone title']]]['start_date'] = $value[$header_arr['start date']];
      ////						$milestone_arr[$value[$header_arr['milestone title']]]['end_date'] = $value[$header_arr['end date']];
      ////						unset($value[$header_arr['milestone title']]);
      ////						unset($value[$header_arr['milestone description']]);
      ////						unset($value[$header_arr['start date']]);
      ////						unset($value[$header_arr['end date']]);
      //					}else {
      //						$mtitle = 'default';
      //					}
      if (isset($value[$header_arr['title']]) && trim($value[$header_arr['title']])) {
      foreach ($value as $k => $v) {
      $task_ass[strtolower($fileds[$k])] = $v;

      // Parsing each data for error in data
      if (strtolower($fileds[$k]) == 'type' && $v) {
      if (in_array(strtolower($v), $task_type_arr)) {
      $task_error[strtolower($fileds[$k])] = 0;
      } else {
      $task_error[strtolower($fileds[$k])] = 1;
      }
      } elseif (strtolower($fileds[$k]) == 'status' && $v) {
      if (in_array(strtolower($v), $task_status_arr)) {
      $task_error[strtolower($fileds[$k])] = 0;
      } else {
      $task_error[strtolower($fileds[$k])] = 1;
      }
      } elseif (strtolower($fileds[$k]) == 'due date' && $v) {
      if ($this->Format->isValidDateTime($v)) {
      $task_error[strtolower($fileds[$k])] = 0;
      } else {
      $task_error[strtolower($fileds[$k])] = 1;
      }
      } elseif (strtolower($fileds[$k]) == 'assigned to' && strtolower($v) != 'me' && $v) {
      if (in_array($v, $task_assign_to_users)) {
      $task_error[strtolower($fileds[$k])] = 0;
      } else {
      $task_error[strtolower($fileds[$k])] = 1;
      }
      } else {
      $task_error[strtolower($fileds[$k])] = 0;
      }
      }
      $task[] = $task_ass;
      $task_err[] = $task_error;
      }
      }
      $i++;
      }
      fclose($handle);
      }
      //pr($milestone_arr);echo "<hr/>";pr($task);echo "<hr/>";pr($task_err);exit;
      //$this->set('milestone_arr',$milestone_arr);

      $this->Project->recursive = -1;
      $projectdata = $this->Project->findById($project_id);

      $this->set('projectname', $projectdata['Project']['name']);
      $this->set('task', $task);
      $this->set('task_err', $task_err);
      $this->set('preview_data', 1);
      $this->set('fileds', $fileds);
      $this->set('porj_id', $project_id);
      $this->set('porj_uid', $project_uid);
      $this->set('csv_file_name', $csv_info['name']);
      $this->set('total_rows', $linecount);
      $this->render('importexport');
      } else {
      $this->Session->write("ERROR", __("Please import a valid CSV file"));
      $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
      }
      } else {
      $this->Session->write("ERROR", __("Please import a valid CSV file"));
      $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
      }
      } */
    function csv_dataimport() {
        $this->Session->write('csvimportflag', 1);
        $project_id = $this->data['proj_id'];
        $project_uid = $this->data['proj_uid'];
        $task_type_arr = array('enhancement', 'enh', 'bug', 'research n do', 'rnd', 'quality assurance', 'qa', 'unit testing', 'unt', 'maintenance', 'mnt', 'others', 'oth', 'release', 'rel', 'update', 'upd', 'development', 'dev');
        //$task_status_arr = array('new', 'close', 'wip', 'resolve', 'resolved', 'closed');
        $task_status_arr = array();
        $this->loadModel('User');
        $this->loadModel('ProjectUser');
        $this->Project->recursive = -1;
        if ($project_id != 'all') {
            // to get list of statuses present in the selected projects 
            $status_name = $this->Project->query("SELECT status.name AS Name from statuses as status LEFT JOIN projects as project ON status.workflow_id = project.workflow_id WHERE project.id =" . $project_id . " ORDER BY status.seq_order ASC");
            foreach ($status_name as $s => $sn) {
                $task_status_arr[] = strtolower($sn['status']['Name']);
            }
            $task_is_billabe = array(0, 1);
            $task_assign_to_userid = $this->ProjectUser->find('list', array('conditions' => array('company_id' => SES_COMP, 'project_id' => $project_id), 'fields' => 'user_id'));
            $task_assign_to_users = $this->User->find('list', array('conditions' => array('id' => $task_assign_to_userid, 'isactive' => 1), 'fields' => 'email'));
        }
        //$fields_arr = array('milestone title','milestone description','start date','end date','title','description','due date','status','type','assigned to');
        $fields_arr = array('project', 'milestone', 'title', 'description', 'due date', 'status', 'type', 'assigned to', 'estimated hour', 'start time', 'end time', 'break time', 'is billable');
        $fields_arr1 = array('project name', 'tasks#', 'title', 'description', 'status', 'estimated hours', 'type', 'milestone', 'assigned to', 'due date', 'created by', 'date created', 'last updated', 'comments');
        if (isset($_FILES['import_csv'])) {
            $ext = pathinfo($_FILES['import_csv']['name'], PATHINFO_EXTENSION);
            //if(in_array($_FILES['import_csv']['type'],$mimes)){
            if (strtolower($ext) == 'csv') {
                $csv_info = $_FILES['import_csv'];
                //Uploading the csv file to Our server
                $file_name = SES_ID . "_" . $project_id . "_" . $csv_info['name'];
                @copy($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);

                $row = 1;
                // Counting total rows and Restricting from uploading a file having more then 1000 record
                $linecount = count(file(CSV_PATH . "task_milstone/" . $file_name));
                if ($linecount > 1001) {
                    @unlink($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
                    $this->Session->write("ERROR", __("Please split the file and upload again. Your file contain more than 1000 rows"));
                    $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                    exit;
                }
                if ($csv_info['size'] > 2097152) {
                    @unlink($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
                    $this->Session->write("ERROR", __("Please upload a file with size less then 2MB"));
                    $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                    exit;
                }
                //Parsing the csv file
                if (($handle = fopen(CSV_PATH . "task_milstone/" . $file_name, "r")) !== FALSE) {
                    $i = 0;
                    $j = 0;
                    $separator = ',';
                    $chk_coma = $data = fgetcsv($handle, 1000, ",");
                    if (count($chk_coma) == 1 && stristr($chk_coma[0], ";")) {
                        $separator = ';';
                    }
                    rewind($handle);
                    $project_list = array();
                    $project_name = array();
                    $j = 0;
                    while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                        if (strtolower(trim($data[0])) == '' || strtolower(trim($data[0])) == 'export date' || strtolower(trim($data[0])) == 'total')
                            continue;
                        if (!$i) {
                            // Check for column count
                            if (count($data) >= 1) {
                                // Check for exact number of fields 
                                foreach ($data AS $key => $val) {
                                    if (!in_array(strtolower($val), $fields_arr) && !in_array(strtolower($val), $fields_arr1)) {
                                        @unlink(CSV_PATH . "task_milstone/" . $file_name);
                                        $this->Session->write("ERROR", __("Invalid CSV file").", <a href='" . HTTP_ROOT . "projects/download_sample_csvfile' style='text-decoration:underline;color:#0000FF'>Download</a>".__("and check with our sample file"));
                                        $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                                        exit;
                                    }
                                    if (strtolower($val) == 'tasks#' || strtolower($val) == 'comments' || strtolower($val) == 'last updated' || strtolower($val) == 'date created' || strtolower($val) == 'created by') {
                                        continue;
                                    } else {
                                        $fileds[$key] = $val;
                                }
                                }
                                //$fileds = $data;
                                //$header_arr = array_flip($data);
                                foreach ($data AS $key => $val) {
                                    $header_arr[strtolower($val)] = $key;
                                }
                            } else {
                                @unlink(CSV_PATH . "task_milstone/" . $file_name);
                                $this->Session->write("ERROR", __("Require atleast Task Title column to import the Tasks"));
                                $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                                exit;
                            }
                        } else {
                            $value = $data;
                            if (isset($value[$header_arr['title']]) && trim($value[$header_arr['title']]) || isset($value[$header_arr['task title']]) && trim($value[$header_arr['task title']]) && $value[$header_arr['task#']] != 'Export Date' && $value[$header_arr['task#']] != 'Total') {
                                foreach ($value as $k => $v) {
                                    if (strtolower($fileds[$k]) == 'task#')
                                        continue;
                                    $task_ass[strtolower($fileds[$k])] = $v;
                                    if ($project_id == 'all' && (strtolower($fileds[$k]) == 'project' || strtolower($fileds[$k]) == 'project name') && empty($v)) {
                                        @unlink(CSV_PATH . "task_milstone/" . $file_name);
                                        $this->Session->write("ERROR", __("Invalid CSV file").", <a href='" . HTTP_ROOT . "projects/download_sample_csvfile' style='text-decoration:underline;color:#0000FF'>Download</a> ".__("and check with our sample file"));
                                        $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_id);
                                    }
                                    if (strtolower($fileds[$k]) == 'project' || strtolower($fileds[$k]) == 'project name' && $v) {
                                        $project_data_arr = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name'), 'conditions' => array('Project.isactive' => 1, 'Project.company_id' => SES_COMP)));
                                        $project_data_arr = array_flip($project_data_arr);
                                        $project_data_arr = array_change_key_case($project_data_arr, CASE_LOWER);
                                        $project_data_arr = array_flip($project_data_arr);
                                        if (in_array(strtolower(trim($v)), $project_data_arr)) {
                                            $project_name[] = ucwords($v);
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                        if ($project_id == 'all') {
                                            $project = $this->Project->find('first', array('conditions' => array('Project.name LIKE ' => trim(strtolower($v)))));
                                            $project_list[$j] = $project;
                                            if (!empty($project)) {
                                                $pro_id = $project['Project']['id'];
                                                $status_name = $this->Project->query("SELECT status.name AS Name from statuses as status LEFT JOIN projects as project ON status.workflow_id = project.workflow_id WHERE project.id =" . $pro_id . " ORDER BY status.seq_order ASC");
                                                foreach ($status_name as $s => $sn) {
                                                    $task_status_arr[] = strtolower($sn['status']['Name']);
                                                }
                                                $task_is_billabe = array(0, 1);
                                                $task_assign_to_userid = $this->ProjectUser->find('list', array('conditions' => array('company_id' => SES_COMP, 'project_id' => $pro_id), 'fields' => 'user_id'));
                                                $task_assign_to_users = $this->User->find('list', array('conditions' => array('id' => $task_assign_to_userid, 'isactive' => 1), 'fields' => 'email'));
                                                $j++;
                                            } else {
                                                $status_name = $this->Project->query("SELECT status.name AS Name from statuses as status WHERE status.workflow_id =0 ORDER BY status.seq_order ASC");
                                                foreach ($status_name as $s => $sn) {
                                                    $task_status_arr[] = strtolower($sn['status']['Name']);
                                            }
                                                $task_is_billabe = array(0, 1);
                                                $j++;
                                        }
                                    }
                                    }

                                    // Parsing each data for error in data 
                                    else if (strtolower($fileds[$k]) == 'type' || strtolower($fileds[$k]) == 'task type' && $v) {
                                        if (in_array(strtolower(trim($v)), $task_type_arr)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'status' && $v) {
                                        if (in_array(strtolower($v), $task_status_arr)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'due date' && $v) {
                                        if ($this->Format->isValidDateTime($v)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'assigned to' && strtolower($v) != 'me' && $v) {
                                        if (in_array($v, $task_assign_to_users)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'estimated hour' && $v) {
                                        if ($this->Format->isValidDateHours($v)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'start time' && $v) {
                                        if ($this->Format->isValidTlDateHours($v, 1)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'end time' && $v) {
                                        if ($this->Format->isValidTlDateHours($v, 1)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'break time' && $v) {
                                        if ($this->Format->isValidDateHours($v)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } elseif (strtolower($fileds[$k]) == 'is billable' && $v) {
                                        if (in_array(trim($v), $task_is_billabe)) {
                                            $task_error[strtolower($fileds[$k])] = 0;
                                        } else {
                                            $task_error[strtolower($fileds[$k])] = 1;
                                        }
                                    } else {
                                        $task_error[strtolower($fileds[$k])] = 0;
                                    }
                                }
                                $task[] = $task_ass;
                                $task_err[] = $task_error;
                            } else {
                                @unlink(CSV_PATH . "task_milstone/" . $file_name);
                                $this->Session->write("ERROR", __("Invalid CSV file").", <a href='" . HTTP_ROOT . "projects/download_sample_csvfile' style='text-decoration:underline;color:#0000FF'>Download</a> ".__("and check with our sample file"));
                                $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                                exit;
                            }
                        }
                        $i++;
                    }
                    fclose($handle);
                }
                if ($project_id != 'all') {
                    $this->Project->recursive = -1;
                    $projectdata = $this->Project->findById($project_id);
                    $projectname = $projectdata['Project']['name'];
                } else {
                    $project_name = array_unique($project_name);
                    if (!empty($project_name)) {
                        $numItems = count($project_name);
                        $k = 0;
                        $pro_name = '';
                        $pro_name_last = '';
                        foreach ($project_name as $key => $value) {
                            if (++$k === $numItems && count($project_name) > 1) {
                                $pro_name_last = ' And ' . $value;
                            } else {
                                $pro_name .= $value . ',';
                            }
                        }
                    }
                    $projectname = trim($pro_name, ',') . $pro_name_last;
                }
                //pr($milestone_arr);echo "<hr/>";pr($task);echo "<hr/>";pr($task_err);exit;
                //$this->set('milestone_arr',$milestone_arr);
                $this->set('projectname', $projectname);
                $this->set('porj_id', $project_id);
                $this->set('porj_uid', $project_uid);
//                } else {
                $this->set(compact('project_list'));
//                }
                $this->set('pro_type', $project_id);
                $this->set('task', $task);
                $this->set('task_err', $task_err);
                $this->set('preview_data', 1);
                $this->set('fileds', $fileds);
                $this->set('csv_file_name', $csv_info['name']);
                $this->set('total_rows', $linecount);
                $this->set('new_file_name', $file_name);
                $this->render('importexport');
            } else {
                $this->Session->write("ERROR", __("Please import a valid CSV file"));
                $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                exit;
            }
        } else {
            $this->Session->write("ERROR", __("Please import a valid CSV file"));
            $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
            exit;
        }
    }

    /**
     * @method public confirm_import Dataimport Interface 
     */
    function confirm_import($id = null) {
//        pr($this->request->data); exit;
        if (!empty($this->Session->read('csvimportflag'))) {
        $pro_id = trim($this->data['project_id']);
        if (trim($this->data['project_id']) != 'all') {
            $project_id = trim($this->data['project_id']);
            $validProject = $this->Project->find('first', array('conditions' => array('Project.id' => $project_id), 'fields' => 'Project.id'));
            if (empty($validProject)) {
                    $this->Session->write('ERROR', __("Oops! Error occured in importing task"));
                $this->redirect($_SERVER['HTTP_REFERER']);
            }
            $task_assign_to_userid = $this->ProjectUser->find('list', array('conditions' => array('company_id' => SES_COMP, 'project_id' => $project_id), 'fields' => 'user_id'));
            $task_assign_to_users = $this->User->find('list', array('conditions' => array('id' => $task_assign_to_userid, 'isactive' => 1), 'fields' => 'email'));
            // to get list of statuses present in the selected projects 
        }
        //$milestone_arr = unserialize($this->data['milestone_arr']);
        $task_arr = null;
        $con_val = 'allproject';
        if (trim($this->data['new_file_name']) != '') {
            if (($handle = fopen(CSV_PATH . "task_milstone/" . trim($this->data['new_file_name']), "r")) !== FALSE) {
                $i = 0;
                $j = 0;
                $separator = ',';
                $chk_coma = $data = fgetcsv($handle, 1000, ",");
                if (count($chk_coma) == 1 && stristr($chk_coma[0], ";")) {
                    $separator = ';';
                }
                rewind($handle);
                $project_list = array();
                $j = 0;
                while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                    if (!$i) {
                        // Check for column count
                        if (count($data) >= 1) {
                            $fileds = $data;
                            foreach ($data AS $key => $val) {
                                $header_arr[strtolower($val)] = $key;
                            }
                        }
                    } else {

                        // Verifing data
                            if ($pro_id != 'all' && (strlen($data[$header_arr['title']]) != 0 || strlen($data[$header_arr['task title']]) != 0)) {
                        $value = $data;
                            } else if ($pro_id == 'all' && (strlen($data[$header_arr['project']]) != 0 || strlen($data[$header_arr['project name']]) != 0) && (strlen($data[$header_arr['title']]) != 0 || strlen($data[$header_arr['task title']]) != 0)) {
                                $value = $data;
                            } else {
                                continue;
                            }
                            $assign_to = !empty($value[$header_arr['assigned to']]) ? $value[$header_arr['assigned to']] : '';
                            if ((isset($value[$header_arr['title']]) && trim($value[$header_arr['title']])) || (isset($value[$header_arr['task title']]) && trim($value[$header_arr['task title']]) && $value[$header_arr['task#']] != 'Export Date' && $value[$header_arr['tasks#']] != 'Total')) {
                            foreach ($value as $k => $v) {
                                    if (strtolower($fileds[$k]) == 'tasks#' || strtolower($fileds[$k]) == 'created by' || strtolower($fileds[$k]) == 'date created' || strtolower($fileds[$k]) == 'last updated' || strtolower($fileds[$k]) == 'comments')
                                        continue;
                                $task_ass[strtolower($fileds[$k])] = $v;
                                    if ((strtolower($fileds[$k]) == 'project' || strtolower($fileds[$k]) == 'project name') && !empty($v)) {
                                    $project_id1 = $this->Project->getProjectId(trim(strtolower($v)));
                                    if (!empty($project_id1)) {
                                            if ($assign_to) {
                                                $this->checkUser($project_id1, $assign_to);
                                            }
                                        $project_list[$j] = $project_id1;
                                        $project_list_data[trim(strtolower($v))] = $project_id1;
                                        $j++;
                                        } else {
                                            $proId = $this->createProject($v);
                                            if (empty($proId)) {
                                                $this->Session->write("ERROR", __("Error creating project"));
                                                $this->redirect(HTTP_ROOT . "projects/importexport/" . $project_uid);
                                            } else {
                                                if ($assign_to) {
                                                    $this->checkUser($proId, $assign_to);
                                    }
                                                $project_list[$j] = $proId;
                                                $project_list_data[trim(strtolower($v))] = $proId;
                                                $j++;
                                            }
                                        }
                                    if ($pro_id != 'all') {
                                        $con_val = 'sproject';
                                    }
                                }
                            }
                            $task_arr[] = $task_ass;
                        }
                    }
                    $i++;
                }
                fclose($handle);
            }
        }
        if ($pro_id != 'all') {
            $project_list[0] = $project_id;
        }
        $project_list = array_unique($project_list);
        if (!empty($project_list_data)) {
            $project_list_data = array_unique($project_list_data);
        }
        $project_name = array();
        foreach ($project_list as $pkey => $pval) {
            $project_name[trim(strtolower($this->Format->getProjectName($pval)))] = $this->Format->getProjectName($pval);
            $task_assign_to_userid = $this->ProjectUser->find('list', array('conditions' => array('company_id' => SES_COMP, 'project_id' => $pval), 'fields' => 'user_id'));
            $task_assign_to_users = $this->User->find('list', array('conditions' => array('id' => $task_assign_to_userid, 'isactive' => 1), 'fields' => 'email'));
            $status_name = $this->Project->query("SELECT status.name AS Name, status.id AS legend from statuses as status LEFT JOIN projects as project ON status.workflow_id = project.workflow_id WHERE project.id =" . $pval . " ORDER BY status.seq_order ASC");
            #echo "<pre>";print_r($task_arr);exit;
            $this->loadModel('Milestone');
            //$this->loadModel('EasycaseMilestone');
            $EasycaseMilestone = ClassRegistry::init('EasycaseMilestone');
            $EasycaseMilestone->recursive = -1;
            //Get the Case no. for the existing projects
            $caseNoArr = $this->Easycase->find('first', array('conditions' => array('Easycase.project_id' => $pval), 'fields' => array('MAX(Easycase.case_no) as caseno')));
            $caseNo = $caseNoArr[0]['caseno'] + 1;
            $hind = 0;
            // Preparing history data
//            $history[$hind++]['total_task'] = count($task_arr);
//            $total_valid_rows = $total_valid_rows ? ($total_valid_rows + count($task_arr)) : count($task_arr);
//            $total_valid_rows = $total_valid_rows ? ($total_valid_rows + count($task_arr)) : count($task_arr);
            $results_titles = Hash::extract($task_arr, '{n}.milestone');
            $results_titles = array_values(array_filter($results_titles));
            $array_milston_ids = array();
            #echo "<pre>";print_r($results_titles);exit;
            if (!empty($results_titles)) {
                $results_titles = array_unique($results_titles);
                $exist_milestones = $this->Milestone->find('list', array('conditions' => array('Milestone.title' => $results_titles, 'Milestone.project_id' => $pval, 'Milestone.company_id' => SES_COMP), 'fields' => array('Milestone.id', 'Milestone.title')));

                foreach ($results_titles as $key => $val) {
                    $milestone = array();
                    if (!in_array(trim($val), $exist_milestones)) {
                        $milestone['title'] = trim($val);
                        $milestone['description'] = '';
                        $milestone['project_id'] = $pval;
                        $milestone['user_id'] = SES_ID;
                        $milestone['company_id'] = SES_COMP;
                        $milestone['uniq_id'] = md5(uniqid());
                        $this->Milestone->create();
                        $this->Milestone->save($milestone);
                        $milestone_last_insert_id = $this->Milestone->getLastInsertID();
                        $array_milston_ids[$milestone['title']] = $milestone_last_insert_id;
                    } else {
                        $milestone_last_insert_id = array_search($val, $exist_milestones);
                        if (!in_array($milestone_last_insert_id, $array_milston_ids)) {
                            $array_milston_ids[trim($val)] = $milestone_last_insert_id;
                        }
                    }
                }
            }
        }
//        pr($array_milston_ids); exit;
        $default = 1;
        $milestone_id = '';
        $non_existing_typ = null;
        $non_existing_typ_with = null;
        $no_task = 0;
        foreach ($task_arr as $k => $v) {
//            pr($task_arr); exit;
            $csv_pro_name = !empty($v['project']) ? trim(strtolower($v['project'])) : '';
                $csv_pro_name = empty($csv_pro_name) ? trim(strtolower($v['project name'])) : $csv_pro_name;
            $projectId = !empty($project_list_data[$csv_pro_name]) ? $project_list_data[$csv_pro_name] : '';
            $project_id = !empty($project_id) ? $project_id : '';
            $map = array(
                "allproject" => $project_id == $projectId || $pro_id != 'all',
                "sproject" => $project_id == $projectId && $pro_id != 'all'
            );
            if ($pro_id == 'all') {
                $pro_name = !empty($project_name[$csv_pro_name]) ? trim(strtolower($project_name[$csv_pro_name])) : '';
                $map = array(
                    "allproject" => $pro_name == $csv_pro_name || $pro_id != 'all',
                    "sproject" => $projectId == $projectId && $pro_id != 'all'
                );
            }
            if ($map[$con_val]) {
                $pval = !empty($projectId) ? $projectId : $project_id;
//              if(trim(strtolower($v['project']) == $this->Format->getProjectName($pval)){
                if (isset($v['milestone']) && trim($v['milestone']) && strtolower(trim($v['milestone'])) != 'default') {
                    $default = 0;
                    $milestone_id = $array_milston_ids[trim($v['milestone'])];
                    } else if (trim($v['milestone']) == '') {
                    $default = 1;
                } else if (strtolower(trim($v['milestone'])) == 'default') {
                    $default = 1;
                }
                    if (!trim($v['title']) && !trim($v['task title']))
                    continue;
                    $title = !empty($v['title']) ? $v['title'] : '';
                    $easycase['title'] = empty($title) ? $v['task title'] : $title;
                $easycase['message'] = (isset($v['description']) && $v['description']) ? $v['description'] : '';
                $due_date = (isset($v['due date']) && $v['due date']) ? $v['due date'] : '';
                //$this->Format->isValidDateTime($due_date);
                if ($due_date) {
                    $due_date = $this->Format->isValidDateTime($due_date) ? date('Y-m-d', strtotime($due_date)) : '';
                }
                $easycase['due_date'] = $due_date;
                /* if (!isset($v['status'])){
                  $legend = 1;
                  }else{
                  if ($v['status'] && (strtoupper(trim($v['status'])) == 'WIP')) {
                  $legend = 2;
                  } elseif ($v['status'] && ((strtolower(trim($v['status'])) == 'close') || (strtoupper(trim($v['status'])) == 'CLOSED'))) {
                  $legend = 3;
                  } elseif ($v['status'] && (strtolower(trim($v['status'])) == 'resolve' || strtolower(trim($v['status'])) == 'resolved')) {
                  $legend = 5;
                  } else {
                  $legend = 1;
                  }
                  } */
                foreach ($status_name as $kys => $tsknme) {
                    $legend = $status_name[0]['status']['legend'];
                    if ($v['status'] && (strtolower(trim($v['status'])) == strtolower(trim($tsknme['status']['Name'])))) {
                        $legend = $tsknme['status']['legend'];
                        break;
                    }
                }
                $easycase['legend'] = $legend;
                $easycase['previous_legend'] = $legend;
                if (!isset($v['type'])) {
                    if (isset($GLOBALS['TYPE'])) {
                        $easycase['type_id'] = isset($GLOBALS['TYPE'][0]) ? $GLOBALS['TYPE'][0]['Type']['id'] : $GLOBALS['TYPE'][1]['Type']['id'];
                    } else {
                        $easycase['type_id'] = 2;
                    }
                } else {
                    $t_tak_typ = $this->get_type_id($v['type']);
                    if (stristr($t_tak_typ, "___")) {
                        $t_tak_typ_t = explode('___', $t_tak_typ);
                        $easycase['type_id'] = $t_tak_typ_t[0];
                        if (!$non_existing_typ_with) {
                            $non_existing_typ_with = $t_tak_typ_t[2];
                        }
                        if (!$non_existing_typ) {
                            $non_existing_typ = array($t_tak_typ_t[1]);
                        } else {
                            if (!in_array($t_tak_typ_t[1], $non_existing_typ)) {
                                array_push($non_existing_typ, $t_tak_typ_t[1]);
                            }
                        }
                    } else {
                        $easycase['type_id'] = $t_tak_typ;
                    }
                }
                if (!isset($v['assigned to'])) {
                    $easycase['assign_to'] = 0;
                } else {
                    if (strtolower($v['assigned to']) != 'me' && $v['assigned to']) {
                        if (array_search($v['assigned to'], $task_assign_to_users)) {
                            $easycase['assign_to'] = array_search($v['assigned to'], $task_assign_to_users);
                        } else {
                                $easycase['assign_to'] = 0;
                        }
                    } else {
                            $easycase['assign_to'] = 0;
                    }
                }
                $easycase['project_id'] = $pval;
                $easycase['user_id'] = SES_ID;
                $easycase['priority'] = 1;
                $easycase['case_no'] = $caseNo++;
                $easycase['uniq_id'] = md5(uniqid());
                $easycase['actual_dt_created'] = GMT_DATETIME;
                $easycase['dt_created'] = GMT_DATETIME;
                $easycase['isactive'] = 1;
                $easycase['format'] = 2;
                if (isset($v['estimated hour'])) {
                    $estimated_hours = trim($v['estimated hour']);
                    if ($estimated_hours != '') {
                        if (defined('TLG') && TLG == 1) {
                            if (strpos($estimated_hours, ':') > -1) {
                                $split_est = explode(':', $estimated_hours);
                                $est_sec = ((($split_est[0]) * 3600) + intval($split_est[1]) * 60);
                            } else if (strpos($estimated_hours, '.') > -1) {
                                $split_est = explode('.', $estimated_hours);
                                $est_sec = ((($split_est[0]) * 3600) + (intval($split_est[1]) * 60) * 60);
                            } else {
                                $est_sec = $estimated_hours * 3600;
                            }
                        } else {
                            if (strpos($estimated_hours, ':') > -1) {
                                $split_est = explode(':', $estimated_hours);
                                $est_sec = $this->mintomin($split_est[0], intval($split_est[1]));
                            } else {
                                $est_sec = $estimated_hours;
                            }
                        }
                        $easycase['estimated_hours'] = $est_sec;
                    } else {
                        $easycase['estimated_hours'] = 0;
                    }
                } else {
                    $easycase['estimated_hours'] = 0;
                }
                $this->Easycase->create();
                $sid = $this->Easycase->save($easycase);
                $no_task++;
                $history[$hind++]['total_task'] = $no_task;
                $total_valid_rows = $no_task;
                $current_id = $this->Easycase->getLastInsertID();
                if (!$default && $milestone_id != '') {
                    $EasycaseMiles = array();
                    $EasycaseMiles['easycase_id'] = $current_id;
                    $EasycaseMiles['milestone_id'] = $milestone_id;
                    $EasycaseMiles['project_id'] = $pval;
                    $EasycaseMiles['user_id'] = SES_ID;
                    $EasycaseMiles['dt_created'] = GMT_DATETIME;
                    $EasycaseMilestone->saveAll($EasycaseMiles);
                }
                if (defined('TLG') && TLG == 1) {
                    if ($current_id && isset($v['start time']) && $this->Format->isValidTlDateHours($v['start time']) && isset($v['end time']) && $this->Format->isValidTlDateHours($v['end time'])) {
                        $task_is_billabe = array(0, 1);
                        $logdata['start_time'] = trim($v['start time']);
                        $logdata['end_time'] = trim($v['end time']);
                        $logdata['break_time'] = isset($v['break time']) ? trim($v['break time']) : 0;
                        if (!$this->Format->isValidDateHours($logdata['break_time'])) {
                            $logdata['break_time'] = 0;
                        }
                        if (isset($v['is billable'])) {
                            $logdata['is_billable'] = in_array(trim($v['is billable']), $task_is_billabe) ? $v['is billable'] : 0;
                        } else {
                            $logdata['is_billable'] = 0;
                        }
                        if ($logdata['start_time'] != '' && $logdata['end_time'] != '') {
                            $this->loadModel('LogTime');
                            // utc has been converted to users time zone
                            $task_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), "date");
                            $i = 0;
                            $LogTime = array();
                            $LogTime[$i]['task_id'] = $current_id;

                                $LogTime[$i]['project_id'] = $pval;
                            $LogTime[$i]['user_id'] = $easycase['assign_to'];
                            $LogTime[$i]['task_status'] = $legend;
                            $LogTime[$i]['ip'] = $_SERVER['REMOTE_ADDR'];

                            // start time set start 
                            $start_time = $logdata['start_time'];
                            $spdts = explode(':', $start_time);

                            #converted to min
                            if ((strpos($start_time, 'am') === false) && (strpos($start_time, 'AM') === false)) {
                                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0] + 12) : $spdts[0];
                                if ((strpos($start_time, 'PM'))) {
                                    $dt_start = trim(strstr($nwdtshr . ":" . $spdts[1], 'PM', true)) . ":00";
                                } else {
                                    $dt_start = trim(strstr($nwdtshr . ":" . $spdts[1], 'pm', true)) . ":00";
                                }
                            } else {
                                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0] ) : '00';
                                if ((strpos($start_time, 'AM'))) {
                                    $dt_start = trim(strstr($nwdtshr . ":" . $spdts[1], 'AM', true)) . ":00";
                                } else {
                                    $dt_start = trim(strstr($nwdtshr . ":" . $spdts[1], 'am', true)) . ":00";
                                }
                            }
                            $minute_start = ($nwdtshr * 60) + $spdts[1];

                            // start time set end 
                            // end time set start 
                            $end_time = $logdata['end_time'];
                            $spdte = explode(':', $end_time);
                            #converted to min
                            if ((strpos($end_time, 'am') === false) && (strpos($end_time, 'AM') === false)) {
                                $nwdtehr = ($spdte[0] != 12) ? ($spdte[0] + 12) : $spdte[0];
                                $dt_end = strstr($nwdtehr . ":" . $spdte[1], 'pm', true) . ":00";
                                if ((strpos($end_time, 'PM'))) {
                                    $dt_end = trim(strstr($nwdtehr . ":" . $spdte[1], 'PM', true)) . ":00";
                                } else {
                                    $dt_end = trim(strstr($nwdtehr . ":" . $spdte[1], 'pm', true)) . ":00";
                                }
                            } else {
                                $nwdtehr = ($spdte[0] != 12) ? ($spdte[0]) : '00';
                                if ((strpos($end_time, 'AM'))) {
                                    $dt_end = trim(strstr($nwdtehr . ":" . $spdte[1], 'AM', true)) . ":00";
                                } else {
                                    $dt_end = trim(strstr($nwdtehr . ":" . $spdte[1], 'am', true)) . ":00";
                                }
                            }
                            $minute_end = ($nwdtehr * 60) + $spdte[1];
                            // end time set end 
                            // checking if start is greater than end then add 24 hr in end i.e. 1440 min 
                            $duration = $minute_end >= $minute_start ? ($minute_end - $minute_start) : (($minute_end + 1440) - $minute_start);
                            $task_end_date = $minute_end >= $minute_start ? $task_date : date('Y-m-d', strtotime($task_date . ' +1 day'));

                            // total working 
                            $totalbreak = trim($logdata['break_time']) != '' ? $logdata['break_time'] : '0';
                            $break_time = trim($totalbreak);
                            if (strpos($break_time, '.')) {
                                $split_break = $break_time * 60;
                                $break_hour = (intval($split_break / 60) < 10 ? "0" : "") . intval($split_break / 60);
                                $break_min = (intval($split_break % 60) < 10 ? "0" : "") . intval($split_break % 60);
                                $break_time = $break_hour . ":" . $break_min;
                                $minute_break = ($break_hour * 60) + $break_min;
                            } elseif (strpos($break_time, ':')) {
                                $split_break = explode(':', $break_time);
                                #converted to min
                                $minute_break = ($split_break[0] * 60) + $split_break[1];
                            } else {
                                $break_time = $break_time . ":00";
                                $minute_break = $break_time * 60;
                            }
                            $minute_break = $duration < $minute_break ? 0 : $minute_break;
                            // break ends 
                            // total hrs start 
                            $total_duration = $duration - $minute_break;
                            $total_hours = $total_duration;
                            // total hrs end 

                            $LogTime[$i]['task_date'] = $task_date;
                            $LogTime[$i]['start_time'] = $dt_start;
                            $LogTime[$i]['end_time'] = $dt_end;

                            // required to convert the date to utc as we are taking converted server date to save 
                            #converted to UTC
                            $LogTime[$i]['start_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_date . " " . $dt_start, "datetime");
                            $LogTime[$i]['end_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_end_date . " " . $dt_end, "datetime");

                            #stored in sec
                            $LogTime[$i]['break_time'] = $minute_break * 60;
                            $LogTime[$i]['total_hours'] = $total_hours * 60;

                            $LogTime[$i]['is_billable'] = $logdata['is_billable'];
                            $LogTime[$i]['description'] = strip_tags(addslashes(trim($CS_message)));

                            $this->LogTime->saveAll($LogTime);
                        }
                    }
                }
            }
        }
//        }
//        exit;
        $project_name = array_unique($project_name);
        if (!empty($project_name)) {
            $numItems = count($project_name);
            $k = 0;
            $pro_name = '';
            $pro_name_last = '';
            foreach ($project_name as $key => $value) {
                if (++$k === $numItems && count($project_name) > 1) {
                    $pro_name_last = ' And ' . $value;
                } else {
                    $pro_name .= $value . ',';
                }
            }
        }
        $pro_name = trim($pro_name, ',') . $pro_name_last;
            $total_task = $this->data['total_rows'] - 1;
        $this->set('total_valid_rows', $total_valid_rows);
        $this->set('csv_file_name', $this->data['csv_file_name']);
            $this->set('total_rows', $total_task);
            $this->set('newtotal_task', $no_task);
            $this->set('proj_name', !empty($this->Format->getProjectName($project_id)) ? $this->Format->getProjectName($project_id) : $pro_name);
        $this->set('history', $history);
        $this->set('non_existing_typ_with', $non_existing_typ_with);
        $this->set('non_existing_typ', $non_existing_typ);
        $this->render('importexport');
        } else {
            $this->Session->write("ERROR", __("Sorry").", " . $this->data['csv_file_name'] . __(" already imported"));
            $this->redirect(HTTP_ROOT . "projects/importexport");
            exit;
        }
        //echo $project_id; pr($milestone_arr);echo "<hr/>";pr($task_arr);exit;
    }

    function mintomin($hr, $min) {
        $ret = $hr;
        if ($min == 0) {
            return 0;
        } else {
            if ($min > 60) {
                $ret = $ret + round(($min / 60), 2);
            } else {
                $ret = $ret + round(($min / 60), 2);
            }
        }
        return $ret;
    }

    /* function confirm_import() {
      $project_id = $this->data['project_id'];
      $this->loadModel('User');
      $this->loadModel('ProjectUser');
      $this->loadModel('Project');
      $task_assign_to_userid = $this->ProjectUser->find('list', array('conditions' => array('company_id' => SES_COMP, 'project_id' => $project_id), 'fields' => 'user_id'));
      $task_assign_to_users = $this->User->find('list', array('conditions' => array('id' => $task_assign_to_userid, 'isactive' => 1), 'fields' => 'email'));

      //$milestone_arr = unserialize($this->data['milestone_arr']);
      $task_arr = unserialize($this->data['task_arr']);
      $this->loadModel('Milestone');
      $this->loadModel('Easycase');
      //$this->loadModel('EasycaseMilestone');
      $EasycaseMilestone = ClassRegistry::init('EasycaseMilestone');
      $EasycaseMilestone->recursive = -1;
      //Get the Case no. for the existing projects
      $caseNoArr = $this->Easycase->find('first', array('conditions' => array('Easycase.project_id' => $project_id), 'fields' => array('MAX(Easycase.case_no) as caseno')));
      $caseNo = $caseNoArr[0]['caseno'] + 1;
      $hind = 0;
      // to get list of statuses present in the selected projects
      $status_name =$this->Project->query("SELECT status.name AS Name, status.id AS legend from statuses as status LEFT JOIN projects as project ON status.workflow_id = project.workflow_id WHERE project.id =".$project_id." ORDER BY status.seq_order ASC");

      // Preparing history data
      //$history[$hind]['milestone_title'] = $key;
      $history[$hind++]['total_task'] = count($task_arr);
      $total_valid_rows = $total_valid_rows ? ($total_valid_rows + count($task_arr)) : count($task_arr);
      foreach ($task_arr as $k => $v) {
      if (!trim($v['title']))
      continue;
      $easycase['title'] = $v['title'];
      $easycase['message'] = (isset($v['description']) && $v['description']) ? $v['description'] : '';
      $due_date = (isset($v['due date']) && $v['due date']) ? $v['due date'] : '';
      //$this->Format->isValidDateTime($due_date);
      if ($due_date) {
      $due_date = $this->Format->isValidDateTime($due_date) ? date('Y-m-d', strtotime($due_date)) : '';
      }
      $easycase['due_date'] = $due_date;
      foreach($status_name as $kys=>$tsknme){
      $legend = $status_name[0]['status']['legend'];
      if ($v['status'] && (strtolower(trim($v['status'])) == strtolower(trim($tsknme['status']['Name'])))) {
      $legend = $tsknme['status']['legend']; break;
      }
      }
      $easycase['legend'] = $legend;
      $easycase['type_id'] = $this->get_type_id($v['type']);
      if (strtolower($v['assigned to']) != 'me' && $v['assigned to']) {
      if (array_search($v['assigned to'], $task_assign_to_users)) {
      $easycase['assign_to'] = array_search($v['assigned to'], $task_assign_to_users);
      } else {
      $easycase['assign_to'] = SES_ID;
      }
      } else {
      $easycase['assign_to'] = SES_ID;
      }
      $easycase['project_id'] = $project_id;
      $easycase['user_id'] = SES_ID;
      $easycase['priority'] = 1;
      $easycase['case_no'] = $caseNo++;
      $easycase['uniq_id'] = md5(uniqid());
      $easycase['actual_dt_created'] = GMT_DATETIME;
      $easycase['dt_created'] = GMT_DATETIME;
      $easycase['isactive'] = 1;
      $easycase['format'] = 2;
      $this->Easycase->create();
      $sid = $this->Easycase->save($easycase);
      }
      //}
      $this->set('total_valid_rows', $total_valid_rows);
      $this->set('csv_file_name', $this->data['csv_file_name']);
      $this->set('total_rows', $this->data['total_rows']);
      $this->set('total_task', count($task_arr));
      $this->set('proj_name', $this->Format->getProjectName($project_id));
      $this->set('history', $history);
      $this->render('importexport');

      //echo $project_id; pr($milestone_arr);echo "<hr/>";pr($task_arr);exit;
      } */

    function get_type_id($type) {
        $type = strtolower($type);
        if ($type == 'bug') {
            return 1;
        } elseif ($type == 'enhancement' || $type == 'enh') {
            return 3;
        } elseif ($type == 'research n do' || $type == 'rnd') {
            return 4;
        } elseif ($type == 'quality assurance' || $type == 'qa') {
            return 5;
        } elseif ($type == 'unit testing' || $type == 'unt') {
            return 6;
        } elseif ($type == 'maintenance' || $type == 'mnt') {
            return 7;
        } elseif ($type == 'others' || $type == 'oth') {
            return 8;
        } elseif ($type == 'release' || $type == 'rel') {
            return 9;
        } elseif ($type == 'update' || $type == 'upd') {
            return 10;
        } else {
            return 2;
        }
    }

    /**
     * @method public download_sample_csv_file  
     */
    function download_sample_csvfile() {
        //$myFile ='demo_sample_milestone_csv_file.csv';
        if (defined('TLG') && TLG == 1) {
            $myFile = 'Orangescrum_Import_Tasktimelog_Sample.csv';
        } else {
            $myFile = 'Orangescrum_Import_Task_Sample.csv';
        }
        header('HTTP/1.1 200 OK');
        header('Cache-Control: no-cache, must-revalidate');
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=Orangescrum_Task_Sample.csv");
        readfile(CSV_PATH . "task_milstone/" . $myFile);
        exit;
    }

    function checkfile_existance() {
        $file_info = $_FILES['file-0'];
        $file_name = SES_ID . "_" . $this->data['porject_id'] . "_" . $file_info['name'];
        //echo $file_name;exit;
        $directory = CSV_PATH . "task_milstone";
        if ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if ($file_name == $entry) {
                        $filesize = filesize($directory . '/' . $file_name);
                        if ($file_info['size'] == $filesize) {
                            $arr['msg'] = __("Already a file with same name and same size of", true) . $filesize . " " . __("bytes exists", true) . ". " . __("Would you like to replace the exsiting file", true) . "?";
                        } else {
                            $arr['msg'] = __("Already file with same name and size of", true) . $filesize . " " . __("bytes exists", true) . ". " . __("Would you like to replace the existing file", true) . "?";
                        }
                        $err = 1;
                        $arr['success'] = 0;
                        $arr['error'] = 1;
                    }
                    //echo "$entry<br/>";
                }
            }
            closedir($handle);
            if (!$err) {
                $arr['success'] = 1;
                $arr['msg'] = "";
                $arr['error'] = 0;
            }
            echo json_encode($arr);
            exit;
        }
    }

    function learnmore() {
        $this->layout = '';
    }

    function project_thumb_view() {
        
    }

    /**
     * 
     */
    function member_list() {
        $this->layout = "ajax";
        $this->loadModel('User');
        $list = $this->User->get_email_list();
        if ($list) {
            foreach ($list as $key => $val) {
                if (trim($val['User']['email']) != '' && trim(strtolower($val['User']['email'])) != 'null') {
                    $name = "";
                    if ($val['User']['name']) {
                        $name = stripcslashes($val['User']['name']);
                    }
                    if ($val['User']['last_name']) {
                        $name .=" " . stripcslashes($val['User']['last_name']);
                    }
                    if ($name) {
                        $email[$val['User']['id']] = $name . " <" . $val['User']['email'] . ">";
                    } else {
                        $email[$val['User']['id']] = $val['User']['email'];
                    }
                }
            }
        }
        //$arr['email'] = array_unique($email);
        echo json_encode(array_unique($email));
        exit;
    }

    /**
     * @method Public onbording($paramName) Onboarding for create project
     * @return  html
     */
    function onbording() {
        if (SES_TYPE > 2) {
            $this->redirect(HTTP_ROOT);
            exit;
        }
        if ($GLOBALS['project_count']) {
            $projectusercls = ClassRegistry::init('ProjectUser');
            $projectusercls->recursive = -1;
            $projectusers = $projectusercls->find('count', array('conditions' => array('company_id' => SES_COMP)));

            $this->set('projectuser_count', $projectusers ? $projectusers : 0);
            $easycase_cls = ClassRegistry::init('Easycase');
            $proje_ids = array_keys($GLOBALS['active_proj_list']);
            $easycase_cls->recursive = -1;
            $task_count = $easycase_cls->find('count', array('conditions' => array('project_id' => $proje_ids)));
            $this->set('task_crted', $task_count ? $task_count : 0);
        }
        $company_usercls = ClassRegistry::init('CompanyUser');
        $totalusers = $company_usercls->find('count', array('conditions' => array('company_id' => SES_COMP, 'is_active !=' => 3)));
        $this->set('totalusers', $totalusers);
        setcookie('LOAD_TW_POP', 1, time() + 3600, '/', DOMAIN_COOKIE, false, false);

        $id = $this->Auth->user('id');
        $this->loadModel('User');
        $rec = $this->User->findById($id);
        if (($rec['User']['dt_last_logout'] == '' && $rec['User']['show_default_inner'])) {
            $this->set('is_log_out', 1);
        }
    }

    function hide_default_inner() {
        $this->loadModel('User');
        $this->User->id = SES_ID;
        $this->User->saveField('show_default_inner', 0);
        echo 'success';
        exit;
    }

    /**
     * @method Public deleteprojects($projuid) Deleting project with all associated data to that project
     * @return bool true/false
     */
    function deleteprojects($projuid = '', $page = NULL) {
        if (SES_TYPE > 2) {
            $grpcount = $this->Project->query('SELECT Project.id FROM projects AS Project WHERE Project.user_id=' . $this->Auth->user('id') . ' AND Project.uniq_id="' . $projuid . '" AND Project.company_id=' . SES_COMP . '');
            if (!$grpcount[0]['Project']['id']) {
                $this->redirect(HTTP_ROOT);
                exit;
            }
        }
        $redirect = HTTP_ROOT . "projects/manage";
        if (isset($page) && (intval($page) > 1)) {
            $redirect.="?page=" . $page;
        }

        if (!$projuid) {
            $this->redirect($redirect);
            exit;
        } else {
            $arr = $this->Project->deleteprojects($projuid);
            if (isset($arr['succ']) && $arr['succ']) {
                $this->Session->write('SUCCESS', $arr['msg']);
            } elseif (isset($arr['error']) && $arr['error']) {
                $this->Session->write('ERROR', $arr['msg']);
            } else {
                $this->Session->write('ERROR', __('Oops! Error occured in deletion of project'));
            }
            $this->redirect($redirect);
            exit;
        }
    }

    function ajax_existuser_delete() {
        $this->layout = 'ajax';
        if (isset($this->params['data']['userid']) && $this->params['data']['userid']) {
            $uid = $this->params['data']['userid'];
            $projId = trim($this->params['data']['project_id']);
            $ProjectUser = ClassRegistry::init('ProjectUser');
            $ProjectUser->unbindModel(array('belongsTo' => array('Project')));
            $checkAvlMem3 = $ProjectUser->find('count', array('conditions' => array('ProjectUser.user_id' => $uid, 'ProjectUser.project_id' => $projId), 'fields' => 'DISTINCT ProjectUser.id'));
            if ($checkAvlMem3) {
                $ProjectUser->query("DELETE FROM project_users WHERE user_id=" . $uid . " AND project_id=" . $projId);
            }
            //Remove from Group update table , that user should not get mail when he is removed from a project.
            $this->loadModel('DailyUpdate');
            $DailyUpdate = $this->DailyUpdate->getDailyUpdateFields($projId, array('DailyUpdate.id', 'DailyUpdate.user_id'));
            if (isset($DailyUpdate) && !empty($DailyUpdate)) {
                $user_ids = explode(",", $DailyUpdate['DailyUpdate']['user_id']);
                if (($index = array_search($uid, $user_ids)) !== false) {
                    unset($user_ids[$index]);
                }
                $du['user_id'] = implode(",", $user_ids);
                $this->DailyUpdate->id = $DailyUpdate['DailyUpdate']['id'];
                $this->DailyUpdate->save($du);
            }
            echo "success";
            exit;
        }
    }

    function generateMsgAndSendPjMail($pjid, $id, $comp, $ses_id = '') {
        if (!empty($ses_id)) {
            $User_id = $ses_id;
        } else {
            $User_id = $this->Auth->user('id');
        }
        $this->loadModel('User');
        $rec = $this->User->findById($User_id);
        $from_name = $rec['User']['name'] . ' ' . $rec['User']['last_name'];

        App::import('helper', 'Casequery');
        $csQuery = new CasequeryHelper(new View(null));

        App::import('helper', 'Format');
        $frmtHlpr = new FormatHelper(new View(null));

        ##### get User Details
        $this->loadModel('User');
        $toUsrArr = $this->User->findById($id);
        $to_email = "";
        $to_name = "";
        if (count($toUsrArr)) {
            $to_email = $toUsrArr['User']['email'];
            $to_name = $frmtHlpr->formatText($toUsrArr['User']['name']);
        }
//                
        ##### get Project Details
        $this->Project->recursive = -1;
        $prjArr = $this->Project->find('first', array('conditions' => array('Project.id' => $pjid), 'fields' => array('Project.name', 'Project.short_name', 'Project.uniq_id')));
        $projName = "";
        $projUniqId = "";
        if (count($prjArr)) {
            $projName = $frmtHlpr->formatText($prjArr['Project']['name']);
            $projUniqId = $prjArr['Project']['uniq_id'];
        }

        $subject = "You have been added to " . $projName . " on Orangescrum";

        $this->Email->delivery = EMAIL_DELIVERY;
        $this->Email->to = $to_email;
        $this->Email->subject = $subject;
        $this->Email->from = FROM_EMAIL_NOTIFY;
        $this->Email->template = 'project_add';
        $this->Email->sendAs = 'html';
        $this->set('to_name', $to_name);
        $this->set('from_name', $from_name);
        $this->set('projName', $projName);
        $this->set('projUniqId', $projUniqId);
        $this->set('multiple', 0);
        $this->set('company_name', $comp['Company']['name']);
        if (!stristr($_SERVER['SERVER_NAME'], "newui.orangescrum.org")) {
            return $this->Sendgrid->sendgridsmtp($this->Email);
        } else {
            return true;
        }
    }

    function default_inner() {
        $this->layout = '';
    }

    /**
     * Showing and Managing task types by company owner
     * 
     * @method task_type
     * @author Orangescrum
     * @return
     * @copyright (c) Aug/2014, Andolsoft Pvt Ltd.
     */
    function task_type() {
        $this->loadModel("Type");
        $task_types = $this->Type->getAllTypes();

        $this->loadModel("TypeCompany");
        $sel_types = $this->TypeCompany->getSelTypes();
        $is_projects = 0;
        if (isset($sel_types) && !empty($sel_types) && isset($task_types) && !empty($task_types)) {
            foreach ($task_types as $key => $value) {
                //if (array_search($value['Type']['id'], $sel_types) || intval($value['Total']['cnt'])) {
                if (array_search($value['Type']['id'], $sel_types)) {
                    $task_types[$key]['Type']['is_exist'] = 1;
                } else {
                    $task_types[$key]['Type']['is_exist'] = 0;
                }
            }
            $is_projects = 1;
        }
        $this->set(compact('task_types', 'sel_types', 'is_projects'));
    }

    /**
     * Add new task types by company owner
     * 
     * @method addNewTaskType
     * @author Orangescrum
     * @return
     * @copyright (c) Aug/2014, Andolsoft Pvt Ltd.
     */
    function addNewTaskType() {
        if (isset($this->data['Type']) && !empty($this->data['Type'])) {

            $data = $this->data['Type'];
            $data['short_name'] = strtolower($data['short_name']);
            $data['company_id'] = SES_COMP;
            $data['seq_order'] = 0;

            $this->loadModel("Type");
            if (isset($data['id']) && $data['id']) {
                
            } else {
                $this->Type->id = '';
            }
            $this->Type->save($data);
            $id = $this->Type->getLastInsertID();
            if (isset($data['id']) && $data['id']) {
                $this->Session->write("SUCCESS", __("Task type") . " '" . trim($data['name']) . "' " . __("updated successfully."));
            } else {
                $this->loadModel("TypeCompany");
                //Check record exists or not while added 1st time. If not then added all default type with new one.
                $isRes = $this->TypeCompany->getTypes();
                $cnt = 0;

                if (isset($isRes) && empty($isRes)) {
                    //Getting default task type
                    $types = $this->Type->getDefaultTypes();
                    foreach ($types as $key => $values) {
                        $data1[$key]['type_id'] = $values['Type']['id'];
                        $data1[$key]['company_id'] = SES_COMP;
                        $cnt++;
                    }
                }

                $data1[$cnt]['type_id'] = $id;
                $data1[$cnt]['company_id'] = SES_COMP;
                $this->TypeCompany->saveAll($data1);
                $this->Session->write("SUCCESS", __("Task type") . " '" . trim($data['name']) . "' " . __("added successfully."));
            }
        } else {
            $this->Session->write("ERROR", __("Error in addition of task type."));
        }
        $this->redirect(HTTP_ROOT . "task-type");
    }

    /**
     * Save selected task types by company owner
     * 
     * @method saveTaskType
     * @author Orangescrum
     * @return
     * @copyright (c) Aug/2014, Andolsoft Pvt Ltd.
     */
    function saveTaskType() {
        if (isset($this->data['Type']) && !empty($this->data['Type'])) {
            $this->loadModel("TypeCompany");

            $this->TypeCompany->query("DELETE FROM type_companies WHERE company_id=" . SES_COMP);
            foreach ($this->data['Type'] as $key => $value) {
                $data['company_id'] = SES_COMP;
                $data['type_id'] = $value;

                $this->TypeCompany->id = '';
                $this->TypeCompany->save($data);
            }
            $this->Session->write("SUCCESS", __("Task type saved successfully."));
        } else {
            $this->Session->write("ERROR", __("Error in saving of task type."));
        }
        $this->redirect(HTTP_ROOT . "task-type");
    }

    /**
     * Delete task types by company owner
     * 
     * @method deleteTaskType
     * @author Orangescrum
     * @return boolean
     * @copyright (c) Aug/2014, Andolsoft Pvt Ltd.
     */
    function deleteTaskType() {
        $this->layout = 'ajax';
        $id = $this->params['data']['id'];
        if (intval($id)) {
            $this->loadModel("Type");
            $this->Type->id = $id;
            $this->Type->delete();

            $this->loadModel("TypeCompany");
            $this->TypeCompany->query("DELETE FROM type_companies WHERE type_id=" . $id . " AND company_id=" . SES_COMP);

            echo 1;
        } else {
            echo 0;
        }
        exit;
    }

    function validateTaskType() {
        $jsonArr = array('status' => 'error');
        if (!empty($this->request['data']['name'])) {
            $this->loadModel("Type");
            $count_type = $this->Type->find('first', array('conditions' => array('OR' => array('Type.short_name' => trim($this->request['data']['sort_name']), 'Type.name' => trim($this->request['data']['name'])), 'Type.id !=' => trim($this->request['data']['id'])), 'fields' => array("Type.name", "Type.short_name")));
            if (!$count_type) {
                $jsonArr['status'] = 'success';
            } else {
                if (strtolower($count_type['Type']['short_name']) == strtolower(trim($this->request['data']['sort_name']))) {
                    $jsonArr['msg'] = 'sort_name';
                }
                if (strtolower($count_type['Type']['name']) == strtolower(trim($this->request['data']['name']))) {
                    $jsonArr['msg'] = 'name';
                }
            }
        }
        echo json_encode($jsonArr);
        exit;
    }

    function checkfile_csv_validation() {
        if (!empty($this->request->data) && !empty($_FILES)) {
            $project_id = $this->request->data['proj_id'];
            if (isset($_FILES['import_csv'])) {
                $ext = pathinfo($_FILES['import_csv']['name'], PATHINFO_EXTENSION);
                if (strtolower($ext) == 'csv') {
                    $csv_info = $_FILES['import_csv'];
                    $file_name = $csv_info['name'];
                    @copy($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
                    if (($handle = fopen(CSV_PATH . "task_milstone/" . trim($file_name), "r")) !== FALSE) {
                        $flag = '';
                        $data = fgetcsv($handle, 1000, ",");
                        foreach ($data as $key => $val) {
                            if (preg_match('/project/', trim(strtolower($val)))) {
                                $flag = 1;
                                break;
                            } else {
                                $flag = 0;
                            }
                        }
                        if (!empty($flag)) {
                                echo 1;
                                exit;
                            } else {
                                echo 2;
                                exit;
                            }
                        }
                    }
                } else {
                    echo 3;
                    exit;
                }
            } else {
                echo 4;
                exit;
            }
        }

    function delete_file() {
        if (!empty($this->request->data['file_name'])) {
            $file_name = $this->request->data['file_name'];
            unlink(CSV_PATH . "task_milstone/" . $file_name);
            exit;
        }
    }

    function createProject($proName) {
        $createProject = null;
        $createProject['new_template'] = 0;
        $createProject['Project']['name'] = $proName;
        $createProject['Project']['task_type'] = 0;
        $createProject['Project']['description'] = '';
        $createProject['Project']['members'][0] = SES_ID;
        $createProject['Project']['members_list'] = '';
        $createProject['Project']['estimated_hours'] = '';
        $createProject['Project']['start_date'] = '';
        $createProject['Project']['end_date'] = '';
        $createProject['Project']['validate'] = 1;
        $createProject['Project']['click_referer'] = '';
        $shortname = $this->acronym($proName);
        $createProject['Project']['short_name'] = $shortname;
//        pr($createProject); exit;
        $proId = $this->add_project($createProject);
        return $proId;
    }

    function acronym($longname) {
        $newstring = $longname . '0123456789';
        $newstring = str_replace(' ', '', $newstring);
        $letters = array();
        $words = explode(' ', $longname);
        foreach ($words as $word) {
            $word = (substr($word, 0, 1));
            array_push($letters, $word);
        }
        $shortname = strtoupper(implode($letters));
//        $company_id = $this->Company->find('first', array('fields' => array('Company.id'), 'conditions' => array('Company.uniq_id' => COMP_UID)));
        $company_id = SES_COMP;
        $projects = $this->Project->find('list', array('fields' => array('Project.name', 'Project.short_name'), 'conditions' => array("Project.company_id" => $company_id)));
        $newshortname = $shortname;

        foreach ($projects as $key => $val) {
            if (in_array($newshortname, $projects)) {
                $newshortname = '';
                for ($i = strlen($shortname); $i < 5; $i++) {
                    $randomChar .= strtoupper($newstring[mt_rand(0, strlen($newstring) - 1)]);
                }
                $newshortname = $shortname . $randomChar;
            } else {
                $newshortname = $shortname . $randomChar;
            }
        }
        return $newshortname;
    }

    function check_multiple_project() {
        if (!empty($this->request->data) && !empty($_FILES['import_csv'])) {
            $project_id = $this->request->data['proj_id'];
            if ($project_id != 'all') {
                if (isset($_FILES['import_csv'])) {
                    $ext = pathinfo($_FILES['import_csv']['name'], PATHINFO_EXTENSION);
                    if (strtolower($ext) == 'csv') {
                        $csv_info = $_FILES['import_csv'];
                        $file_name = $csv_info['name'];
                        @copy($csv_info['tmp_name'], CSV_PATH . "task_milstone/" . $file_name);
                        if (($handle = fopen(CSV_PATH . "task_milstone/" . trim($file_name), "r")) !== FALSE) {
                            $i = 0;
                            $separator = ',';
                            $chk_coma = $data = fgetcsv($handle, 1000, ",");
                            if (count($chk_coma) == 1 && stristr($chk_coma[0], ";")) {
                                $separator = ';';
                            }
                            rewind($handle);
                            $j = 0;
                            $flag = '';
                            while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                                if (!$i) {
                                    if (count($data) >= 1) {
                                        $fileds = $data;
                                        foreach ($data AS $key => $val) {
                                            $header_arr[strtolower($val)] = $key;
                                        }
                                    }
                                } else {
                                    $value = $data;
                                    if ((isset($value[$header_arr['title']]) && trim($value[$header_arr['title']])) || (isset($value[$header_arr['task title']]) && trim($value[$header_arr['task title']]) && $value[$header_arr['task#']] != 'Export Date' && $value[$header_arr['task#']] != 'Total')) {
                                        foreach ($value as $k => $v) {
                                            if (strtolower($fileds[$k]) == 'task#')
                                                continue;
                                            if ((strtolower($fileds[$k]) == 'project' || strtolower($fileds[$k]) == 'project name') && !empty($v)) {
                                                $project_id1 = $this->Project->getProjectId(trim(strtolower($v)));
                                                if ($project_id1 != $project_id) {
                                                    $flag = 'more_pro';
                                                    break;
                                                } else {
                                                    $flag = 'exists';
                                                    break;
                                                }
                                            } else {
                                                $flag = 'no_project';
                                            }
                                        }
                                    }
                                }
                                $i++;
                            }
                            fclose($handle);
                            echo $flag;
                            exit;
                        }
                    }
                } else {
                    echo 3;
                    exit;
                }
            } else {
                $flag = 'no_project';
                echo $flag;
                exit;
            }
        } else {
            echo 0;
            exit;
        }
    }

    function change_prj_est_hr() {
        $this->layout = "ajax";
        $project_id = $this->data['project_id'];
        $estimated_hr['Project']['estimated_hours'] = $this->data['estimated_hr'];
        $this->Project->id = $project_id;
        if ($this->Project->save($estimated_hr)) {
            echo "success";
        } else {
            echo "fail";
        }
        exit;
    }

    function export_project_listing($srch = Null) {
        $this->layout = "ajax";
        $query = '';
        if ($_COOKIE['project_view'] == "inactive-grid") {
            $query = "AND Project.isactive='2'";
        } else {
            $query = "AND Project.isactive='1'";
        }

        if (isset($_GET['project']) && $_GET['project']) {
            $query .= " AND Project.uniq_id='" . $_GET['project'] . "'";
        }
        $query .= " AND Project.company_id='" . SES_COMP . "'";
        if (isset($_GET['prj']) && $_GET['prj']) {
            $_GET['prj'] = chr($_GET['prj']);
            $pj = $_GET['prj'] . "%";
            $query .= " AND Project.name LIKE '" . addslashes($pj) . "'";
        }
        if (SES_TYPE == 3) {
            $all_assigned_proj = $this->Project->query('SELECT project_id FROM project_users WHERE user_id=' . $this->Auth->user('id') . ' AND company_id=' . SES_COMP);
            if ($all_assigned_proj) {
                $all_assigned_proj = Hash::extract($all_assigned_proj, '{n}.project_users.project_id');
                $all_assigned_proj = array_unique($all_assigned_proj);
                $query .= " AND (Project.user_id=" . $this->Auth->user('id') . " OR Project.id IN(" . implode(',', $all_assigned_proj) . "))";
                $user_cnd = " AND (Project.user_id=" . $this->Auth->user('id') . " OR Project.id IN(" . implode(',', $all_assigned_proj) . "))";
            } else {
                $query .= " AND Project.user_id=" . $this->Auth->user('id');
                $user_cnd = " AND Project.user_id=" . $this->Auth->user('id');
            }
        }
        if (isset($srch) && !empty($srch)) {
            $query .= " AND (Project.name LIKE '%" . $srch . "%' OR Project.short_name LIKE '%" . $srch . "%' OR Project.description LIKE '%" . $srch . "%' ) ";
        }
        $wfl_cndn = '';
        if (defined('TSG') && TSG == 1) {
            $wfl_cndn = ',workflow_id, (select workflows.name from workflows where workflows.id = Project.workflow_id) as wname';
        }
        if (SES_TYPE == 3) {
            // $query .= " AND Project.user_id=" . $this->Auth->user('id');
            // echo "SELECT SQL_CALC_FOUND_ROWS Project.*" .$wfl_cndn. ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,(select ROUND(SUM(easycases.hours), 1) as hours from easycases where easycases.project_id=Project.id and easycases.reply_type='0' and easycases.isactive='1') as totalhours,(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
            //and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size FROM case_files WHERE case_files.project_id=Project.id) AS storage_used FROM projects AS Project WHERE Project.name!='' " . $query . " ORDER BY name ASC $limit";exit;

            $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS Project.*" . $wfl_cndn . ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,(select ROUND(SUM(easycases.hours), 1) as hours from easycases where easycases.project_id=Project.id and easycases.reply_type='0' and easycases.isactive='1') as totalhours,(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
	and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size FROM case_files WHERE case_files.project_id=Project.id) AS storage_used FROM projects AS Project WHERE Project.name!='' " . $query . " ORDER BY name ASC $limit");
        } else {

            $prjAllArr = $this->Project->query("SELECT SQL_CALC_FOUND_ROWS Project.*" . $wfl_cndn . ",(select count(easycases.id) as tot from easycases where easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') as totalcase,(select ROUND(SUM(easycases.hours), 1) as hours from easycases where easycases.project_id=Project.id and easycases.reply_type='0' and easycases.isactive='1') as totalhours,(select count(company_users.id) as tot from company_users, project_users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1
	and project_users.project_id = Project.id) as totusers,(SELECT SUM(case_files.file_size) AS file_size  FROM case_files WHERE case_files.project_id=Project.id) AS storage_used FROM projects AS Project WHERE name!='' " . $query . " ORDER BY name ASC $limit");
        }

        //echo "<pre>";print_r($prjAllArr);exit;
        App::import('helper', 'Format');
        $frmtHlpr = new FormatHelper(new View(null));
        $print_to_csv = "Project Name,Short Name,Description,Start Date,End Date,Estimated Hour(s),Total Number of Days,Total Tasks,Hours spent,Total Users,Storage used\n";
        foreach ($prjAllArr as $k => $project) {
            $strt_date = (isset($project['Project']['start_date']) && !empty($project['Project']['start_date'])) ? $this->Format->chngdate_csv($project['Project']['start_date']) : $this->Format->chngdate_csv($project['Project']['dt_created']);
            $end_date = (isset($project['Project']['end_date']) && !empty($project['Project']['end_date'])) ? $this->Format->chngdate_csv($project['Project']['end_date']) : 'No end date';

            $estimated_hr = isset($project['Project']['estimated_hours']) && $project['Project']['estimated_hours'] != '0.0' ? $frmtHlpr->format_time_hr_min(($project['Project']['estimated_hours'] * 3600)) : '0.0';
            if ($project['Project']['start_date'] && $project['Project']['end_date']) {
                $strt_dates = strtotime($project['Project']['start_date']); // or your date as well
                $end_dates = strtotime($project['Project']['end_date']);
                $datediff = $end_dates - $strt_dates;
                $dt_diff = floor($datediff / (60 * 60 * 24));
            } else {
                $dt_diff = 0;
            }

            $totUser = !empty($project[0]['totusers']) ? $project[0]['totusers'] : '0';
            $totCase = (!empty($project[0]['totalcase'])) ? $project[0]['totalcase'] : '0';
            $totHours = (!empty($project[0]['totalhours'])) ? $project[0]['totalhours'] : '0';
            $filesize = 0;
            if ($totCase && isset($project[0]['storage_used']) && $project[0]['storage_used']) {
                $filesize = number_format(($project[0]['storage_used'] / 1024), 2);
                if ($filesize != '0.0' || $filesize != '0') {
                    $filesize = $filesize;
                }
            }
            $print_to_csv .= $project['Project']['name'] . "," . $project['Project']['short_name'] . "," . $project['Project']['description'] . "," .'"'. $strt_date . '"'. "," .'"'. $end_date . '"'."," . $estimated_hr . "," . $dt_diff . "," . $totCase . "," . $totHours . "," . $totUser . "," . $filesize . "\n";
        }

        $filename = "project_listing" . date("m-d-Y_H-i-s", time());

        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m-d") . ".csv");
        header("Content-disposition: filename=" . $filename . ".csv");

        print $print_to_csv;
        exit;
    }

    /* function to add project customer */

    function add_prj_customer($customer_data, $prj_id, $sd = '', $sc = '') {
        $this->loadModel('InvoiceCustomer');
        $this->loadModel('UserInvitation');
        $this->loadModel('CompanyUser');
        $this->loadModel('Company');
        $user_id = 0;
        $ses_id = !empty($sd) ? $sd : SES_ID;
        $ses_comp = !empty($sc) ? $sc : SES_COMP;
        $company_data = $this->Company->getCompanyFields(array("id" => $ses_comp), array('uniq_id'));
        $com_uid = !empty($company_data['Company']['uniq_id']) ? $company_data['Company']['uniq_id'] : COMP_UID;
        $email = trim($customer_data['cust_email']);
        if ($email != "") {
            $this->loadModel('User');
            $userdetails = $this->User->findByEmail($email);

            if (is_array($userdetails) && count($userdetails) > 0) {
                $user_id = $userdetails['User']['id'];
                $this->User->id = $user_id;
                $this->User->saveField('is_client', true);
                $client_user = $client_user = array(
                    'email' => trim($customer_data['cust_email']),
                    'password' => '',
                    'name' => $customer_data['cust_fname'] . ' ' . $customer_data['cust_lname'],
                    'short_name' => trim($this->Format->makeShortName($customer_data['cust_fname'], $customer_data['cust_lname'])),
                    'istype' => 3,
                );

                $client_user['isactive'] = 2;
                $client_user['dt_created'] = date('Y-m-d H:i:s');
                $client_user['is_email'] = true;
                $client_user['is_client'] = 1;
                $client_user['uniq_id'] = $this->Format->generateUniqNumber();
            } else {
                $client_user = array(
                    'email' => trim($customer_data['cust_email']),
                    'password' => '',
                    'name' => $customer_data['cust_fname'] . ' ' . $customer_data['cust_lname'],
                    'short_name' => trim($this->Format->makeShortName($customer_data['cust_fname'], $customer_data['cust_lname'])),
                    'istype' => 3,
                );
                $client_user['isactive'] = 2;
                $client_user['dt_created'] = date('Y-m-d H:i:s');
                $client_user['is_email'] = true;
                $client_user['is_client'] = $customer_data['status'] == 'allow' ? 0 : 1;
                $client_user['uniq_id'] = $this->Format->generateUniqNumber();
            }
        }
        if (trim($customer_data['cust_fname']) != '') {
            $customer = array(
                'first_name' => trim($customer_data['cust_fname']),
                'last_name' => trim($customer_data['cust_lname']) != '' ? trim($customer_data['cust_lname']) : NULL,
                'street' => trim($customer_data['cust_street']) != '' ? trim($customer_data['cust_street']) : NULL,
                'city' => trim($customer_data['cust_city']) != '' ? trim($customer_data['cust_city']) : NULL,
                'state' => trim($customer_data['cust_state']) != '' ? trim($customer_data['cust_state']) : NULL,
                'country' => trim($customer_data['cust_country']) != '' ? trim($customer_data['cust_country']) : NULL,
                'zipcode' => trim($customer_data['cust_zipcode']) != "" ? trim($customer_data['cust_zipcode']) : NULL,
                'currency' => trim($customer_data['cust_currency']) != "" ? trim($customer_data['cust_currency']) : NULL,
                'phone' => trim($customer_data['cust_phone']) != "" ? trim($customer_data['cust_phone']) : NULL,
                'email' => trim($customer_data['cust_email']) != "" ? trim($customer_data['cust_email']) : NULL,
                'title' => trim($customer_data['cust_title']) != "" ? trim($customer_data['cust_title']) : NULL,
                'organization' => trim($customer_data['cust_organisation']) != "" ? trim($customer_data['cust_organisation']) : NULL,
                'status' => 'Active',
                'modified' => date("Y-m-d H:i:s")
            );

            $customer['uniq_id'] = $this->Format->generateUniqNumber();
            $customer['company_id'] = $ses_comp;
            $customer['created'] = date("Y-m-d H:i:s");
        }
        if ($this->InvoiceCustomer->save($customer)) {

            $this->User->save($client_user);
            $lastUsrId = $this->User->id;
            if ($lastUsrId && isset($customer_data['status']) && $customer_data['status'] == 'allow') {
                $qstr = $this->Format->generateUniqNumber();
                $comp = $this->Company->findById($ses_comp);

                $InviteUsr['UserInvitation']['invitor_id'] = $ses_id;
                $InviteUsr['UserInvitation']['user_id'] = $lastUsrId;
                $InviteUsr['UserInvitation']['company_id'] = $ses_comp;
                $InviteUsr['UserInvitation']['project_id'] = $prj_id;
                $InviteUsr['UserInvitation']['qstr'] = $qstr;
                $InviteUsr['UserInvitation']['created'] = GMT_DATETIME;
                $InviteUsr['UserInvitation']['is_active'] = 1;
                $InviteUsr['UserInvitation']['user_type'] = 3;

                if ($this->UserInvitation->saveAll($InviteUsr)) {

                    $cmpnyUsr = array();
                    $is_sub_upgrade = 1;
                    // Checking for a deleted user when gets invited again.
                    $compuser = $this->CompanyUser->find('first', array('conditions' => array('user_id' => $userid, 'company_id' => $ses_comp)));
                    if ($compuser && $compuser['CompanyUser']['is_active'] == 3) {

                        $cmpnyUsr['CompanyUser']['id'] = $compuser['CompanyUser']['id'];
                    }
                    $cmpnyUsr['CompanyUser']['user_id'] = $lastUsrId;
                    $cmpnyUsr['CompanyUser']['company_id'] = $ses_comp;
                    $cmpnyUsr['CompanyUser']['company_uniq_id'] = $com_uid;
                    $cmpnyUsr['CompanyUser']['user_type'] = 3;
                    $cmpnyUsr['CompanyUser']['is_active'] = 2;
                    $cmpnyUsr['CompanyUser']['created'] = GMT_DATETIME;



                    if ($this->CompanyUser->saveAll($cmpnyUsr)) {
                        $json_arr['email'] = $customer_data['cust_email'];
                        $json_arr['created'] = GMT_DATETIME;
                        $this->Postcase->eventLog($ses_comp, $ses_id, $json_arr, 25);

                        $comp_user_id = $this->CompanyUser->getLastInsertID();

                        $to = $customer_data['cust_email'];
                        $expEmail = $customer_data['cust_email'];
                        $expName = $expEmail;
                        $loggedin_users = $this->Format->getUserNameForWithooutEmailActive(SES_ID);
                        $fromName = ucfirst($loggedin_users['User']['name']);
                        $fromEmail = $loggedin_users['User']['email'];

                        $ext_user = '';
                        if (@$findEmail['User']['id']) {
                            $subject = $fromName . " " . __("invited you to join", true) . " " . $comp['Company']['name'] . " " . __("on Orangescrum", true);
                            $ext_user = 1;
                        } else {
                            $subject = $fromName . " " . __("invited you to join Orangescrum", true);
                        }
                        $fromEmail = !empty($fromEmail) ? $fromEmail : FROM_EMAIL;
                        $this->Email->delivery = EMAIL_DELIVERY;
                        $this->Email->to = $to;
                        $this->Email->subject = $subject;
                        $this->Email->from = $fromEmail;
                        $this->Email->template = 'invite_user';
                        $this->Email->sendAs = 'html';
                        $this->set('expName', ucfirst($expName));
                        $this->set('qstr', $qstr);
                        $this->set('existing_user', $ext_user);

                        $this->set('company_name', $comp['Company']['name']);
                        $this->set('fromEmail', $fromEmail);
                        $this->set('fromName', $fromName);

                        try {
                            $this->Sendgrid->sendgridsmtp($this->Email);
                        } Catch (Exception $e) {
                            
                        }
                    }
                }
            }
            $this->InvoiceCustomer->read(null, $this->InvoiceCustomer->id);
            $this->InvoiceCustomer->set(array('user_id' => $lastUsrId));
            $this->InvoiceCustomer->save();
            $lastInvcstId = $this->InvoiceCustomer->id;
            $return_id = array($lastUsrId, $lastInvcstId);
        }

        return $return_id;
    }

    function testRRule() {
        $this->layout = 'ajax';
        $recurrenceDetail = $this->request->data['recurrenceDrtails'];
        if ($recurrenceDetail['recurrence_end_type'] != 'date') {
            $recurrenceDetail['recur_end_date'] = '';
        }
        $rRule = $this->Format->getRRule($recurrenceDetail, 'test');
        $occurrences = $rRule->getOccurrences();
        $arr = array();
        if (!empty($occurrences)) {
            $arr['formatted_end_date'] = $occurrences[count($occurrences) - 1]->format('M d, Y');
            $arr['end_date'] = $occurrences[count($occurrences) - 1]->format('Y-m-d');
        } else {
            $arr['formatted_end_date'] = '';
            $arr['end_date'] = '';
        }
        echo json_encode($arr);
        /* foreach ( $rrule as $occurrence ) {
          echo $occurrence->format('D d M Y'),"\n";
          }
          echo $rrule->humanReadable(),"\n"; */
        exit;
    }

    function assign_role() {
        $this->layout = 'ajax';
        $projid = $this->params['data']['pjid'];
        $pjname = urldecode($this->params['data']['pjname']);
        $cntmng = $this->params['data']['cntmng'];
        $query = "";
        if (isset($this->params['data']['name']) && trim($this->params['data']['name'])) {
            $srchstr = addslashes($this->params['data']['name']);
            $query = "AND User.name LIKE '%$srchstr%'";
        }

        $ProjectUser = ClassRegistry::init('ProjectUser');
        $ProjectUser->unbindModel(array('belongsTo' => array('Project')));
        $Role = ClassRegistry::init('Role');
        $Role->unbindModel(array('belongsTo' => array('Company')));
        $roles = $Role->find('list'); //, array('conditions' => array('OR' => array('Role.company_id' => SES_COMP, 'Role.company_id' => 0))));

        if (SES_TYPE == 1) {
            $memsExstArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type,ProjectUser.id, if(ProjectUserRole.role!='',ProjectUserRole.role,CompanyUserRole.role) as role, if(ProjectUserRole.role!='',ProjectUser.role_id,CompanyUser.role_id) as role_id FROM `users` AS User left join company_users AS CompanyUser on CompanyUser.user_id=User.id left join roles AS CompanyUserRole on CompanyUserRole.id=CompanyUser.role_id left join project_users AS ProjectUser on ProjectUser.user_id=User.id left join roles AS ProjectUserRole on ProjectUserRole.id=ProjectUser.role_id WHERE User.isactive='1' AND User.name!='' " . $query . " AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND ProjectUser.project_id=" . $projid . " ORDER BY User.name");
        } else {
            $memsExstArr = $ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type,ProjectUser.id, if(ProjectUserRole.role!='',ProjectUserRole.role,CompanyUserRole.role) as role, if(ProjectUserRole.role!='',ProjectUser.role_id,CompanyUser.role_id) as role_id FROM `users` AS User left join company_users AS CompanyUser on CompanyUser.user_id=User.id left join roles AS CompanyUserRole on CompanyUserRole.id=CompanyUser.role_id left join project_users AS ProjectUser on ProjectUser.user_id=User.id left join roles AS ProjectUserRole on ProjectUserRole.id=ProjectUser.role_id WHERE User.isactive='1' AND User.name!='' " . $query . " AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND ProjectUser.project_id=" . $projid . " ORDER BY User.name");
        }
        $this->set('pjname', $pjname);
        $this->set('projid', $projid);
        $this->set('memsExstArr', $memsExstArr);
        $this->set('roles', $roles);
        $this->set('cntmng', $cntmng);
    }

    function assignProjectUserRole() {
        $this->layout = 'ajax';
        $data = array();
        parse_str($this->request->data['projectroles'], $data);
        $this->loadModel('ProjectUser');

        $this->loadModel('RoleAction');
        $this->loadModel('ProjectAction');
        $project_id = $data['project_id'];
        #pr($data);exit;
        $this->ProjectAction->deleteAll(array('ProjectAction.project_id' => $project_id));
        foreach ($data['data']['ProjectUser']['id'] as $k => $val) {
            $this->ProjectUser->id = $val;
            $this->ProjectUser->saveField('role_id', $data['data']['ProjectUser']['role_id'][$k]);
            $actions = $this->RoleAction->find('all', array('conditions' => array('RoleAction.company_id' => SES_COMP, 'RoleAction.role_id' => $data['data']['ProjectUser']['role_id'][$k]), 'fields' => array('RoleAction.role_id', 'RoleAction.action_id', 'RoleAction.is_allowed')));
            if (!empty($actions)) {
                foreach ($actions as $k => $action) {
                    $action['ProjectAction'] = $action['RoleAction'];
                    $action['ProjectAction']['project_id'] = $project_id;
                    unset($action['RoleAction']);
                    $this->ProjectAction->saveAll($action);
                }
            }
        }
        echo 1;
        exit;
    }

    function manage_role() {
        $this->layout = 'ajax';
        $roleId = $this->request->data['roleId'];
        $projectId = $this->request->data['project_id'];
        $project_name = $this->request->data['prjname'];
        $this->loadModel('ProjectUser');
        $this->loadModel('ProjectAction');
        $this->loadModel('Module');
        $this->loadModel('Role');
        /*  $projectRoles = $this->ProjectUser->find('all', array('conditions' => array('ProjectUser.company_id' => SES_COMP, 'ProjectUser.project_id' => $projectId), 'fields' => 'DISTINCT ProjectUser.role_id as role', 'order' => 'role ASC'));
          $projectRoles = Hash::extract($projectRoles, '{n}.ProjectUser.role'); */
        $this->Role->unbindModel(array('belongsTo' => array('Company', 'RoleGroup'), 'hasMany' => array('RoleAction', 'CompanyUser')));
        $this->Role->bindModel(array('hasMany' => array('ProjectAction' => array('className' => 'ProjectAction', 'foreignKey' => 'role_id', 'conditions' => array('ProjectAction.project_id' => $projectId)))));
        $roles = $this->Role->find('all', array('conditions' => array('Role.id' => $roleId)));
        $module_id = array();
        foreach ($roles as $k => $role) {
            $a = Hash::combine($role['ProjectAction'], '{n}.action_id', '{n}.is_allowed');
            unset($roles[$k]['ProjectAction']);
            $module_id = array_unique(Hash::extract($role['RoleModule'], '{n}.module_id'));
            $roles[$k]['ProjectAction'] = $a;
        }
        #$projectRoles = $this->ProjectAction->find('all', array('conditions' => array('ProjectAction.project_id' => $projectId), 'fields' => array('ProjectAction.action_id', 'ProjectAction.is_allowed')));
        $this->Module->unbindModel(array('belongsTo' => array('Company')));
        $modules = $this->Module->find('all', array('conditions' => array('Module.company_id' => SES_COMP, 'Module.is_active' => 1,'Module.id'=>$module_id)));
        #pr($modules);exit;
        $this->set(compact('roles', 'modules', 'projectId', 'project_name'));
    }

    function checkUser($proId, $assign_to) {
        if (!empty($assign_to)) {
            $user_data = $this->User->find('first', array('fields' => array('User.id'), 'conditions' => array('User.email LIKE' => $assign_to)));
            if (!empty($user_data['User']['id'])) {
                $this->loadModel('CompanyUser');
                $com_user = $this->CompanyUser->find('first', array('fields' => array('CompanyUser.id'), 'conditions' => array('CompanyUser.user_id' => $user_data['User']['id'], 'CompanyUser.company_id' => SES_COMP, 'CompanyUser.is_active' => 1)));
                if (!empty($com_user['CompanyUser']['id'])) {
                    $this->loadModel('ProjectUser');
                    $project_user = $this->ProjectUser->find('first', array("fields" => array('ProjectUser.id'), 'conditions' => array('ProjectUser.user_id' => $user_data['User']['id'], 'ProjectUser.company_id' => SES_COMP, 'ProjectUser.project_id' => $proId)));
                    if (empty($project_user['ProjectUser']['id'])) {
                        $last_project_user = $this->ProjectUser->find('first', array("fields" => array('ProjectUser.id'), 'order' => array('ProjectUser.id' => 'DESC')));
                        $createprojectUser['ProjectUser']['id'] = $last_project_user['ProjectUser']['id'] + 1;
                        $createprojectUser['ProjectUser']['project_id'] = $proId;
                        $createprojectUser['ProjectUser']['company_id'] = SES_COMP;
                        $createprojectUser['ProjectUser']['user_id'] = $user_data['User']['id'];
                        $createprojectUser['ProjectUser']['istype'] = 2;
                        $createprojectUser['ProjectUser']['default_email'] = 1;
                        $createprojectUser['ProjectUser']['dt_visited'] = GMT_DATETIME;
                        $this->ProjectUser->save($createprojectUser);
}
                }
            }
        }
        return true;
    }
    function getProjectMembers($projId = NULL) {
	if(isset($projId)) {
	    return $this->Project->query("SELECT User.id, User.uniq_id, User.name FROM users AS User, company_users AS CompanyUser,project_users AS ProjectUser WHERE User.id=CompanyUser.user_id AND User.id=ProjectUser.user_id AND ProjectUser.project_id='".$projId."' AND CompanyUser.company_id='".SES_COMP."' AND CompanyUser.is_active ='1' ORDER BY User.name ASC");
        }
    }
}

?>