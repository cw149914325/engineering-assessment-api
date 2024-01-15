<?php
/**
 * 邮件队列类
 * 
 * ************************************************
 *    邮件队列添加、邮件发送操作统一由调用本类操作       *
 *    1.邮件按照邮件优先级及队列添加时间发送                      *
 *    2.邮件发送成功则删除队列                                                         *
 *    3.邮件发送失败3次则不再发送                                                 *
 * ************************************************
 * 
 * @date 2012-07-18
 * @author zqy
 *
 */
class MailQueue 
{
	protected $mail_queue_model;
	
	protected $mail_template_path = ''; //邮件模版路径
	
	protected static $mail_templates = array(); //所有邮件模版
	
	public function __construct()
	{
		$this->setDefaultMailTemplate();
		
		$this->mail_queue_model = new MailQueue_model();
	}
	
	/**
	 * 邮件默认模版路径
	 */
	protected function setDefaultMailTemplate()
	{
		$this->mail_template_path = APP_PATH . '/config/mail_template.php';
	}
	
	/**
	 * 设置邮件模版路径
	 * 
	 * @param  $mail_template_path
	 */
	public function setMailTemplate($mail_template_path)
	{
		$this->mail_template_path = $mail_template_path;
	}
	
	/**
	 * 添加邮件队列
	 * 
	 * @param $mail_template
	 * @param $to_mail
	 * @param array $mail_data
	 * @param $level
	 */
	public function addQueue($mail_template, $to_mail, array $mail_data, $level = 0)
	{
		$mail_template = $this->getMailTemplate($mail_template);

		$subject = $mail_template['subject'];
		
		$content = $mail_template['content'];
		
		preg_match_all('/\{(\w+?)\}/si', $content, $tags);

		if (!empty($tags) && is_array($tags))
		{
			foreach ((array) $tags[1] as $tag)
			{
				$content = str_replace('{'.$tag.'}', $mail_data[$tag], $content);
			}
		}
		
		$mail_queue_data = array(
			'to_mail' => $to_mail,
			'subject' => $subject,
			'content' => $content,
			'err_num' => 0,
			'level' => $level,
			'add_time' => time(),
		);

		return $this->mail_queue_model->add($mail_queue_data);
	}
	
	/**
	 * 返回邮件模版
	 * 
	 * @param $template_name
	 */
	protected function getMailTemplate($template_name)
	{
		$mail_templates = $this->getMailTemplates();
		
		return $mail_templates[$template_name];
	}
	
	/**
	 * 返回所有邮件模版
	 */
	public function getMailTemplates()
	{
		if (self::$mail_templates)
		{
			return self::$mail_templates;
		}
		
		$mail_templates = array();
		
		if (file_exists($this->mail_template_path))
		{
			$mail_templates = include_once $this->mail_template_path;
		}
		
		self::$mail_templates = $mail_templates;
		
		return $mail_templates;
	}
	
	/**
	 * 发送邮件
	 */
	public function sendMailByQueue()
	{
		$mail = $this->mail_queue_model->getSendMail();

		if (!empty($mail))
		{
			$queueId = $mail['queue_id'];

			$rs = Communication_Model::send($mail['to_mail'], $mail['subject'], $mail['content']);

			if ($rs == true)
			{
				$this->mail_queue_model->delete($queueId);
			} else 
			{
				$this->mail_queue_model->editErrNum($queueId);
			}
		} else 
		{
			sleep(1);
		}
		
		unset($mail);
		
	}
	
	/**
	 * 修改邮件模版发送错误次数
	 * 
	 * @param  $queue_id
	 * @param  $num
	 */
	public function editQueueErrNum($queue_id)
	{
		return $this->mail_queue_model->editErrNum($queue_id);
	}
	
	/**
	 * 删除邮件队列
	 *
	 * @param $queue_id
	 */
	protected function deleteQueue($queue_id)
	{
		$this->mail_queue_model->delete($queue_id);
	}
}

?>