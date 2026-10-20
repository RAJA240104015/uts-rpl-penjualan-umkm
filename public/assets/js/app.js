document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) {
        event.preventDefault();
      }
    });
  });

  const quantity = document.querySelector('input[name="quantity"]');
  const unitPrice = document.querySelector('input[name="unit_price"]');
  const subtotal = document.querySelector('[data-sale-subtotal]');
  if (quantity && unitPrice && subtotal) {
    const formatRupiah = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
    const updateSubtotal = () => {
      const qty = Number.parseInt(quantity.value, 10);
      const price = Number.parseInt(unitPrice.value, 10);
      const total = Number.isSafeInteger(qty) && Number.isSafeInteger(price) && qty > 0 && price > 0 ? qty * price : 0;
      subtotal.textContent = formatRupiah(Number.isSafeInteger(total) ? total : 0);
    };
    quantity.addEventListener('input', updateSubtotal);
    unitPrice.addEventListener('input', updateSubtotal);
    updateSubtotal();
  }

  document.querySelector('[data-print]')?.addEventListener('click', () => window.print());
});
