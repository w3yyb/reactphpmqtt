<?php
/**
 * @author Oliver Lorenz
 * @since 2015-04-27
 * Time: 17:32
 */

class Context
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var ZMQContext
     */
    protected $context;

    /**
     * @param \React\EventLoop\LoopInterface $loop
     * @param ZMQContext    $context
     */
    public function __construct(LoopInterface $loop, ZMQContext $context = null)
    {
        $this->loop = $loop;

        if (!$context) {
            $context = new ZMQContext();
        }

        $this->context = $context;
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        $result = call_user_func_array(array($this->context, $method), $parameters);

        if ($result instanceof ZMQSocket) {
            $result = $this->wrapSocket($result);
        }

        return $result;
    }

    /**
     * @param ZMQSocket $socket
     *
     * @return SocketWrapper
     */
    protected function wrapSocket(ZMQSocket $socket)
    {
        $wrapped = new SocketWrapper($socket, $this->loop);

        if ($this->isReadableSocketType($socket->getSocketType())) {
            $wrapped->attachReadListener();
        }

        return $wrapped;
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    protected function isReadableSocketType($type)
    {
        $readableTypes = array(
            ZMQ::SOCKET_PULL,
            ZMQ::SOCKET_SUB,
            ZMQ::SOCKET_REQ,
            ZMQ::SOCKET_REP,
            ZMQ::SOCKET_ROUTER,
            ZMQ::SOCKET_DEALER,
        );

        return in_array($type, $readableTypes);
    }
}