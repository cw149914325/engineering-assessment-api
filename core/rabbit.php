<?php
/**
 * RabbitMq消息队列
 * @author liangp@mail.51.com
 */
class Rabbit
{

    private $amqpConnect;
    private $channel;
    private $amqpExchange;
    private $amqpQueue;
    private $attr = array();
    //private $durable=true;	//是否持久化
    private $connected = false;
    static private $available = false;
    private $serverCfg = array('host' => '10.10.10.33', 'vhost' => '/', 'port' => '5672', 'login' => 'test', 'password' => '123456');

    public function __construct($queueName, $exchangeName)
    {

        $this->attr['queueName'] = $queueName;
        $this->attr['exchangeName'] = $exchangeName;
        $this->attr['routingKey'] = $queueName . "." . $exchangeName . ".routingKey";  //约定好 
        //检查依赖amqp扩展是否存在？
        try
        {
            self::$available = @class_exists('AMQPConnection');
        }
        catch (Exception $e)
        {
            self::$available = false;
        }
    }

    private function connect()
    {

        try
        {

            //var_dump(self::$available);exit;
            if (!self::$available)
            {
                return false;
            }


            $this->amqpConnect = new AMQPConnection($this->serverCfg);
            $this->amqpConnect->connect();
            //创建channel
            $this->channel = new AMQPChannel($this->amqpConnect);

            //创建exchange
            $this->amqpExchange = new AMQPExchange($this->channel);

            $this->amqpExchange->setName($this->attr['exchangeName']); //创建名字
            /** exchang类型 AMQP_EX_TYPE_DIRECT , AMQP_EX_TYPE_FANOUT, AMQP_EX_TYPE_TOPIC, AMQP_EX_TYPE_HEADER 4种 * */
            $this->amqpExchange->setType(AMQP_EX_TYPE_DIRECT);
            /** exchang状态标志 AMQP_PASSIVE, AMQP_DURABLE, AMQP_AUTODelete 3种 其中AMQP_DURABLE 表示持久存在 * */
            $this->amqpExchange->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);

            //创建队列
            $this->amqpQueue = new AMQPQueue($this->channel);
            //设置队列名字 如果不存在则添加
            $this->amqpQueue->setName($this->attr['queueName']);
            $this->amqpQueue->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);

            $this->amqpQueue->declare();
            $this->amqpQueue->bind($this->attr['exchangeName'], $this->attr['routingKey']); //将你的队列绑定到routingKey

            $this->connected = true;
            return true;
        }
        catch (Exception $e)
        {
            $this->connected = false;
            return false;
        }
    }

    public function close()
    {
        try
        {

            if ($this->amqpConnect && $this->amqpConnect->isConnected())
            {
                $this->amqpConnect->disconnect();
            }

            $this->connected = false;
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    //填充队列
    public function push($message)
    {
        $ret = false;

        try
        {

            //检查是否已连接
            if (!$this->connected)
            {
                $this->connect();
            }

            //确保已连接
            if ($this->connected)
            {
                $ret = $this->amqpExchange->publish(json_encode($message), $this->attr['routingKey'], AMQP_MANDATORY, array('delivery_mode' => 2, 'timestamp' => time())); //消息持久化
                $this->close();
            }
        }
        catch (Exception $e)
        {
            $ret = false;
        }

        return $ret;
    }

    /**
     * 取出队列的消息
     */
    public function pop()
    {
        $quenMsg = '';
        try
        {

            //检查是否已连接
            if (!$this->connected)
            {
                $this->connect();
            }

            //确保已连接
            if ($this->connected)
            {
                $msg = $this->amqpQueue->get(AMQP_AUTOACK);
                if($msg) $quenMsg = $msg->getBody();
                $this->close();
            }
        }
        catch (Exception $e)
        {
            //$ret = false;
        }
        return $quenMsg;
    }

    /*
      //设置是否持久化处理
      public function setDurable($yn){
      $this->durable = $yn;
      }
     */
}

?>