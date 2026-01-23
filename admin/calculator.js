// Globalny stan kalkulatora
let items = [];
let itemCounter = 0;

// Przełączanie między nowym a istniejącym klientem
document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.btn-toggle').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const type = this.dataset.type;
        if (type === 'new') {
            document.getElementById('newClientForm').style.display = 'block';
            document.getElementById('existingClientForm').style.display = 'none';
        } else {
            document.getElementById('newClientForm').style.display = 'none';
            document.getElementById('existingClientForm').style.display = 'block';
        }
    });
});

// Checkbox firma
document.getElementById('isCompany').addEventListener('change', function () {
    document.getElementById('companyFields').style.display = this.checked ? 'block' : 'none';
});

// Wybór istniejącego klienta
document.getElementById('selectClient').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('clientName').value = option.dataset.name || '';
        document.getElementById('clientEmail').value = option.dataset.email || '';
        document.getElementById('clientPhone').value = option.dataset.phone || '';
        document.getElementById('clientAddress').value = option.dataset.address || '';

        if (option.dataset.company) {
            document.getElementById('isCompany').checked = true;
            document.getElementById('companyFields').style.display = 'block';
            document.getElementById('companyName').value = option.dataset.company;
            document.getElementById('companyNip').value = option.dataset.nip || '';
        }
    }
});

// Przełączanie tabów
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function () {
        const tabName = this.dataset.tab;

        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        document.getElementById(tabName + 'Tab').classList.add('active');
    });
});

// Dodaj usługę
function addServiceItem() {
    const select = document.getElementById('selectService');
    const qty = parseFloat(document.getElementById('serviceQty').value) || 1;

    if (!select.value) {
        alert('Wybierz usługę');
        return;
    }

    const option = select.options[select.selectedIndex];
    const item = {
        id: ++itemCounter,
        type: 'usługa',
        serviceId: select.value,
        name: option.dataset.name,
        quantity: qty,
        unitPrice: parseFloat(option.dataset.price),
        total: qty * parseFloat(option.dataset.price)
    };

    items.push(item);
    renderItems();

    select.value = '';
    document.getElementById('serviceQty').value = 1;
}

// Dodaj produkt
function addProductItem(selectId = 'selectProduct', qtyId = 'productQty') {
    const select = document.getElementById(selectId);
    if (!select) return; // Guard clause

    const qtyInput = document.getElementById(qtyId);
    const qty = parseFloat(qtyInput.value) || 1;

    if (!select.value) {
        alert('Wybierz produkt z listy');
        return;
    }

    const option = select.options[select.selectedIndex];

    // Validate Stock
    let stock = parseInt(option.dataset.stock);
    if (isNaN(stock)) stock = 9999; // Fallback if stock not tracked

    if (qty > stock) {
        alert(`Brak wystarczającej ilości w magazynie! Dostępne: ${stock} szt.`);
        return;
    }

    const item = {
        id: ++itemCounter,
        type: 'produkt',
        productId: select.value,
        name: option.dataset.name,
        quantity: qty,
        unitPrice: parseFloat(option.dataset.price) || 0,
        total: qty * (parseFloat(option.dataset.price) || 0)
    };

    items.push(item);
    renderItems();

    // Reset fields
    select.value = '';
    if (qtyInput) qtyInput.value = 1;

    // Visual feedback
    updateSummary();
}

// Dodaj własną pozycję
function addCustomItem() {
    const name = document.getElementById('customName').value.trim();
    const price = parseFloat(document.getElementById('customPrice').value) || 0;
    const qty = parseFloat(document.getElementById('customQty').value) || 1;

    if (!name) {
        alert('Podaj nazwę pozycji');
        return;
    }

    if (price <= 0) {
        alert('Podaj poprawną cenę');
        return;
    }

    const item = {
        id: ++itemCounter,
        type: 'inne',
        name: name,
        quantity: qty,
        unitPrice: price,
        total: qty * price
    };

    items.push(item);
    renderItems();

    document.getElementById('customName').value = '';
    document.getElementById('customPrice').value = '';
    document.getElementById('customQty').value = 1;
}

// Usuń pozycję
function removeItem(id) {
    items = items.filter(item => item.id !== id);
    renderItems();
}

// Renderuj listę pozycji
function renderItems() {
    const container = document.getElementById('itemsList');

    if (items.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Brak pozycji. Dodaj usługę, produkt lub własną pozycję.</p>
            </div>
        `;
        updateSummary();
        return;
    }

    let html = '';
    items.forEach(item => {
        html += `
            <div class="item-card">
                <div class="item-info">
                    <div class="item-name">
                        <i class="fas fa-${item.type === 'usługa' ? 'tools' : item.type === 'produkt' ? 'box' : 'plus'}"></i>
                        ${item.name}
                    </div>
                    <div class="item-details">
                        ${item.quantity} × ${item.unitPrice.toFixed(2)} zł
                    </div>
                </div>
                <span class="item-price">${item.total.toFixed(2)} zł</span>
                <button class="btn-remove" onclick="removeItem(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });

    container.innerHTML = html;
    updateSummary();
}

// Aktualizuj podsumowanie
function updateSummary() {
    const subtotal = items.reduce((sum, item) => sum + item.total, 0);
    const taxRate = 0.23;
    const netto = subtotal / (1 + taxRate);
    const tax = subtotal - netto;

    document.getElementById('subtotalAmount').textContent = netto.toFixed(2) + ' zł';
    document.getElementById('taxAmount').textContent = tax.toFixed(2) + ' zł';
    document.getElementById('totalAmount').textContent = subtotal.toFixed(2) + ' zł';
}

// Zapisz fakturę
async function saveInvoice() {
    // Walidacja
    const clientName = document.getElementById('clientName').value.trim();

    if (!clientName) {
        alert('Podaj dane klienta');
        return;
    }

    if (items.length === 0) {
        alert('Dodaj przynajmniej jedną pozycję');
        return;
    }

    // Przygotuj dane
    const data = {
        client: {
            name: clientName,
            email: document.getElementById('clientEmail').value.trim(),
            phone: document.getElementById('clientPhone').value.trim(),
            address: document.getElementById('clientAddress').value.trim(),
            nip: document.getElementById('companyNip').value.trim(),
            company: document.getElementById('companyName').value.trim()
        },
        invoiceType: document.getElementById('invoiceType').value,
        paymentMethod: document.getElementById('paymentMethod').value,
        notes: document.getElementById('notes').value.trim(),
        items: items
    };

    // Zapisz
    try {
        const response = await fetch('save_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('Dokument został zapisany pomyślnie!');

            // Otwórz PDF
            if (result.pdfUrl) {
                window.open(result.pdfUrl, '_blank');
            }

            // Przekieruj do listy faktur
            window.location.href = 'invoices.php';
        } else {
            alert('Błąd: ' + (result.message || 'Nie udało się zapisać dokumentu'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Wystąpił błąd podczas zapisywania dokumentu');
    }
}

// Wyczyść kalkulator
function clearCalculator() {
    if (!confirm('Czy na pewno chcesz wyczyścić kalkulator?')) {
        return;
    }

    items = [];
    itemCounter = 0;

    document.getElementById('clientName').value = '';
    document.getElementById('clientEmail').value = '';
    document.getElementById('clientPhone').value = '';
    document.getElementById('clientAddress').value = '';
    document.getElementById('companyName').value = '';
    document.getElementById('companyNip').value = '';
    document.getElementById('isCompany').checked = false;
    document.getElementById('companyFields').style.display = 'none';
    document.getElementById('notes').value = '';

    document.getElementById('selectClient').value = '';

    renderItems();
}