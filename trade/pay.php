<?php
// 接收并验证参数
$goodid = isset($_GET['goodid']) ? htmlspecialchars($_GET['goodid']) : '';
$goodname = isset($_GET['goodname']) ? htmlspecialchars($_GET['goodname']) : '';
$count = isset($_GET['count']) ? intval($_GET['count']) : 0;
$price = isset($_GET['price']) ? floatval($_GET['price']) : 0.0;
$total = isset($_GET['total_price']) ? floatval($_GET['total_price']) : 0.0;
$email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : '';

// 处理商品名称，去掉“【自动发货】”等多余内容
$goodname = preg_replace('/【.*?】/', '', $goodname); // 去掉【】及其内容
$goodname = trim($goodname); // 去掉前后空格

// 验证数据有效性
if(empty($goodid) || $count <= 0 || $price <= 0 || $total <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("非法请求：参数不完整或格式不正确");
}

// 计算USDT金额
$usdtAmount = number_format($total / 7.2, 2);
?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USDT(TRC-20)支付</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        text-align: center;
        background-color: #f5f5f5;
        color: #333;
      }

      .logo {
        display: block;
        margin: 0 auto 20px;
        max-width: 100px;
        height: auto;
      }

      .container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }

      .order-info {
        margin-bottom: 20px;
      }

      .order-info h3 {
        margin-bottom: 10px;
        font-size: 18px;
        color: #333;
      }

      .order-info p {
        margin: 5px 0;
        font-size: 14px;
        color: #666;
      }

      .title {
        font-size: 16px;
        color: #333;
        margin-bottom: 10px;
      }

      .instructions {
        font-size: 14px;
        color: #e74c3c;
        margin-bottom: 20px;
      }

      .divider {
        height: 1px;
        background-color: #ddd;
        margin: 20px 0;
      }

      .amount-box {
        margin-bottom: 20px;
      }

      .amount {
        font-size: 24px;
        font-weight: bold;
        color: #27ae60;
      }

      .amount-currency {
        font-size: 16px;
        color: #666;
      }

      .address {
        font-size: 14px;
        color: #333;
        background-color: #f9f9f9;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
        cursor: pointer;
      }

      .address:hover {
        background-color: #f1f1f1;
      }

      .qrcode {
        margin-bottom: 20px;
        font-size: 14px;
        color: #666;
      }

      .timer {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
      }

      .timer-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
      }

      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
      }

      .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
      }

      .modal h2 {
        font-size: 20px;
        color: #333;
        margin-bottom: 10px;
      }

      .modal p {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
      }

      .modal-button {
        background-color: #27ae60;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
      }

      .modal-button:hover {
        background-color: #219653;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <!-- 新增 Logo -->
      <img src="/public/static/images/USDT.png" alt="USDT Logo" class="logo" />

      <div class="order-info">
        <h3>订单信息</h3>
        <p>
          商品:          
          <?php echo $goodname; ?>
        </p>
        <p>
          数量:
          <?php echo $count; ?>
          件
        </p>
        <p>
          单价:
          <?php echo number_format($price, 2); ?>
          元
        </p>
        <p>
          总价:
          <?php echo number_format($total, 2); ?>
          元 (≈<?php echo $usdtAmount; ?>
          USDT)
        </p>
        <p>
          联系方式:
          <?php echo $email; ?>
        </p>
      </div>

      <div class="title">
        请扫描二维码或点击金额和地址粘贴转账<br />USDT(trc-20)支付。
      </div>

      <div class="instructions">
        转账金额必须为下方显示的金额且需要在<br />
        倒计时内完成转账，否则无法被系统确认！
      </div>

      <div class="divider"></div>

      <div class="amount-box">
        <div class="amount">
          <span class="amount-number" id="usdtAmount"
            ><?php echo $usdtAmount; ?></span
          >
          <span class="amount-currency">USDT.TRC20</span>
        </div>
      </div>

      <div class="address" id="usdt-address">
      TJWiLiMYebD9opTVqsbWeNnn3QvVPpxXnY
      </div>

      <div class="qrcode" style="text-align: center;">
    <img src="../../public/static/images/pay.png" alt="USDT 支付二维码" style="max-width: 200px; width: 100%; height: auto; display: inline-block;" />
</div>



      <div class="divider"></div>

      <div class="timer" id="timer">00 : 20 : 00</div>
      <div class="timer-label">
        <span>时</span>
        <span>分</span>
        <span>秒</span>
      </div>
    </div>

    <!-- 超时提示模态框 -->
    <div class="modal" id="timeoutModal">
      <div class="modal-content">
        <h2>支付超时</h2>
        <p>您未在规定时间内完成支付，请重新发起支付。</p>
        <button class="modal-button" id="reloadButton">重新发起支付</button>
      </div>
    </div>

    <script>
      // 修复后的计时器功能
      function startCountdown() {
        const duration = 20 * 60; // 20分钟（单位：秒）
        let endTime = Math.floor(Date.now() / 1000) + duration;

        const timerElement = document.getElementById("timer");
        const timeoutModal = document.getElementById("timeoutModal");
        const reloadButton = document.getElementById("reloadButton");

        // 页面隐藏时暂停计时
        let hiddenStartTime;
        document.addEventListener("visibilitychange", function () {
          if (document.hidden) {
            hiddenStartTime = Date.now();
          } else if (hiddenStartTime) {
            const hiddenDuration = Math.floor(
              (Date.now() - hiddenStartTime) / 1000
            );
            endTime += hiddenDuration;
            hiddenStartTime = undefined;
            updateTimer();
          }
        });

        reloadButton.addEventListener("click", function () {
          location.reload();
        });

        function updateTimer() {
          const now = Math.floor(Date.now() / 1000);
          let remaining = endTime - now;

          if (remaining <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = "00 : 00 : 00";
            timerElement.style.color = "#e74c3c";
            timeoutModal.style.display = "flex";
            return;
          }

          const hours = Math.floor(remaining / 3600);
          remaining %= 3600;
          const minutes = Math.floor(remaining / 60);
          const seconds = remaining % 60;

          timerElement.textContent = `${hours
            .toString()
            .padStart(2, "0")} : ${minutes
            .toString()
            .padStart(2, "0")} : ${seconds.toString().padStart(2, "0")}`;

          if (hours === 0 && minutes < 5) {
            timerElement.style.color = "#e74c3c";
          }
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
      }

      // 页面加载后初始化
window.addEventListener("DOMContentLoaded", function () {
  startCountdown();

  // 复制地址功能
  document
    .getElementById("usdt-address")
    .addEventListener("click", function () {
      const address = this.textContent.trim();
      if (navigator.clipboard) {
        navigator.clipboard.writeText(address)
          .then(() => alert("地址已复制到剪贴板: " + address))
          .catch(() => {
            // 如果 navigator.clipboard 失败，使用备选方案
            copyToClipboardFallback(address);
          });
      } else {
        // 如果 navigator.clipboard 不可用，使用备选方案
        copyToClipboardFallback(address);
      }
    });

  // 备选复制方案
  function copyToClipboardFallback(text) {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed"; // 避免滚动到页面底部
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand("copy");
      alert("地址已复制到剪贴板: " + text);
    } catch (err) {
      alert("复制失败，请手动复制地址: " + text);
    } finally {
      document.body.removeChild(textarea);
    }
  }
});

      
    </script>
  </body>
</html>
