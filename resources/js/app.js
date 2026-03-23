import './bootstrap';
import { initAdmin } from './admin';
import { initShop } from './shop';

document.addEventListener('DOMContentLoaded', () => {
    initShop();
    void initAdmin();
});
