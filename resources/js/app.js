import './bootstrap';
import 'flowbite';
import Swal from 'sweetalert2';
import * as simpleDatatables from 'simple-datatables';
import Alpine from 'alpinejs'

window.simpleDatatables = simpleDatatables;
window.Swal = Swal;

window.Alpine = Alpine
Alpine.start()