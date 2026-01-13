let currentUser = null;

async function init() {
    currentUser = await checkAuth();
    
    if (currentUser && currentUser.is_admin) {
        document.getElementById('admin-link').style.display = 'block';
        document.getElementById('admin-link').href = '/admin.php';
    }
}

async function payWithStripe() {
    try {
        const response = await fetch('/api/payment.php?action=create_stripe_session', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        
        const data = await response.json();
        
        if (data.success && data.session_url) {
            window.location.href = data.session_url;
        } else {
            alert('فشل إنشاء جلسة الدفع. تأكد من إعداد Stripe API Key.');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

async function payWithPayPal() {
    try {
        const response = await fetch('/api/payment.php?action=create_paypal_order', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        
        const data = await response.json();
        
        if (data.success && data.order_id) {
            window.open(`https://www.sandbox.paypal.com/checkoutnow?token=${data.order_id}`, '_blank');
        } else {
            alert('فشل إنشاء طلب الدفع. تأكد من إعداد PayPal API.');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

init();
