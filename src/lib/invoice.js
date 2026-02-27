import { jsPDF } from 'jspdf';

export function generateInvoice(order) {
    const doc = new jsPDF();
    const w = doc.internal.pageSize.getWidth();
    const margin = 20;
    let y = 20;

    // Colors
    const primary = [59, 130, 246];
    const accent = [6, 214, 160];
    const dark = [15, 15, 25];
    const gray = [148, 163, 184];

    // Header background
    doc.setFillColor(...dark);
    doc.rect(0, 0, w, 55, 'F');

    // Gradient accent line
    doc.setFillColor(...primary);
    doc.rect(0, 0, w, 3, 'F');

    // Company name
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.text('SNEAKERHEADS', margin, y + 12);

    // Invoice label
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...gray);
    doc.text('TAX INVOICE', w - margin, y + 5, { align: 'right' });

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.text(`#INV-${order.id?.slice(0, 8)?.toUpperCase() || '00000000'}`, w - margin, y + 14, { align: 'right' });

    doc.setFontSize(9);
    doc.setTextColor(...gray);
    doc.text(`Date: ${new Date(order.created_at || Date.now()).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}`, w - margin, y + 22, { align: 'right' });

    y = 65;

    // Bill To / Ship To
    doc.setFontSize(9);
    doc.setTextColor(...gray);
    doc.text('BILL TO / SHIP TO', margin, y);
    y += 8;

    doc.setTextColor(30, 30, 30);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text(order.address?.address_line1 || 'Address', margin, y);
    y += 6;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    if (order.address?.address_line2) {
        doc.text(order.address.address_line2, margin, y);
        y += 5;
    }
    doc.text(`${order.address?.city || ''}, ${order.address?.state || ''} ${order.address?.postal_code || ''}`, margin, y);
    y += 5;
    doc.text(order.address?.country || 'India', margin, y);
    y += 5;

    // Order info on right
    doc.setFontSize(9);
    doc.setTextColor(...gray);
    doc.text('ORDER DETAILS', w - margin - 80, 65);
    doc.setTextColor(30, 30, 30);
    doc.setFontSize(10);
    doc.text(`Order ID: ${order.id?.slice(0, 8) || 'N/A'}`, w - margin - 80, 73);
    doc.text(`Status: ${(order.status || 'pending').toUpperCase()}`, w - margin - 80, 80);
    doc.text(`Payment: ${(order.payment_method || 'UPI').toUpperCase()}`, w - margin - 80, 87);

    y += 10;

    // Items table header
    doc.setFillColor(245, 247, 250);
    doc.roundedRect(margin, y, w - 2 * margin, 10, 2, 2, 'F');

    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...gray);
    doc.text('ITEM', margin + 5, y + 7);
    doc.text('SIZE', w - margin - 70, y + 7);
    doc.text('QTY', w - margin - 40, y + 7);
    doc.text('AMOUNT', w - margin - 5, y + 7, { align: 'right' });

    y += 15;

    // Items
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(30, 30, 30);
    const items = order.items || [];
    items.forEach((item) => {
        const sneaker = item.sneaker || {};
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(`${sneaker.brand || 'Sneaker'} ${sneaker.model || ''}`, margin + 5, y);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text(`${sneaker.size || '-'} UK`, w - margin - 70, y);
        doc.text('1', w - margin - 40, y);
        const price = formatCurrency(sneaker.price || item.price_at_time || 0);
        doc.text(price, w - margin - 5, y, { align: 'right' });
        y += 8;

        // Divider
        doc.setDrawColor(235, 235, 240);
        doc.line(margin + 5, y, w - margin - 5, y);
        y += 5;
    });

    y += 5;

    // Totals
    const subtotal = order.total_amount || items.reduce((s, i) => s + (i.sneaker?.price || i.price_at_time || 0), 0);
    const shipping = 0;
    const codFee = order.payment_method === 'cod' ? 49 : 0;
    const total = subtotal + codFee;

    const totalX = w - margin - 5;
    const labelX = w - margin - 80;

    doc.setFontSize(10);
    doc.setTextColor(...gray);
    doc.text('Subtotal', labelX, y);
    doc.setTextColor(30, 30, 30);
    doc.text(formatCurrency(subtotal), totalX, y, { align: 'right' });
    y += 7;

    doc.setTextColor(...gray);
    doc.text('Shipping', labelX, y);
    doc.setTextColor(...accent);
    doc.text('FREE', totalX, y, { align: 'right' });
    y += 7;

    if (codFee > 0) {
        doc.setTextColor(...gray);
        doc.text('COD Fee', labelX, y);
        doc.setTextColor(30, 30, 30);
        doc.text(formatCurrency(codFee), totalX, y, { align: 'right' });
        y += 7;
    }

    // Total highlight
    doc.setFillColor(...primary);
    doc.roundedRect(labelX - 5, y - 1, w - margin - labelX + 10, 12, 2, 2, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.text('TOTAL', labelX, y + 8);
    doc.text(formatCurrency(total), totalX, y + 8, { align: 'right' });

    y += 25;

    // Footer
    doc.setDrawColor(235, 235, 240);
    doc.line(margin, y, w - margin, y);
    y += 10;

    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...gray);
    doc.text('Thank you for shopping with Sneakerheads!', w / 2, y, { align: 'center' });
    y += 5;
    doc.text('Every pair is authenticated before delivery. For support, contact us at support@sneakerheads.in', w / 2, y, { align: 'center' });
    y += 5;
    doc.text('www.sneakerheads.in', w / 2, y, { align: 'center' });

    doc.save(`Sneakerheads-Invoice-${order.id?.slice(0, 8) || 'order'}.pdf`);
}

function formatCurrency(amount) {
    return '₹' + Number(amount).toLocaleString('en-IN');
}
