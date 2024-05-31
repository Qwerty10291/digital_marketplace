try{
    document.getElementById('depositButton').addEventListener('click', function () {
    document.getElementById('modalTitle').innerText = 'Deposit Funds';
    document.getElementById('transaction_type').value = 'deposit';
    document.getElementById('modalButton').innerText = 'Deposit';
    document.getElementById('transactionModal').classList.remove('hidden');
});
}catch {}

try{
    document.getElementById('withdrawButton').addEventListener('click', function () {
    document.getElementById('modalTitle').innerText = 'Withdraw Funds';
    document.getElementById('transaction_type').value = 'withdraw';
    document.getElementById('modalButton').innerText = 'Withdraw';
    document.getElementById('transactionModal').classList.remove('hidden');
});
}catch {}


document.getElementById('closeModalButton').addEventListener('click', function () {
    document.getElementById('transactionModal').classList.add('hidden');
});