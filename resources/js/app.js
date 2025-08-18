import './bootstrap';
import './charts';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Make Chart.js available globally
window.Chart = Chart;

window.Alpine = Alpine;
Alpine.start();
