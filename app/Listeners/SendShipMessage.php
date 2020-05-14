<?php

namespace App\Listeners;

use App\Events\DoShip;
use App\Notifications\DoShipNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendShipMessage implements ShouldQueue
{

    /**
     * Handle the event.
     *
     * @param  DoShip  $event
     * @return void
     */
    public function handle(DoShip $event)
    {
        // 从事件对象中取出对应的订单
        $order = $event->getOrder();

        // 调用 notify 方法来发送通知
        $order->user->notify(new DoShipNotification($order));
    }
}
