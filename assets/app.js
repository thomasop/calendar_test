/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import * as bootstrap from 'bootstrap';
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import 'bootstrap';

import { startStimulusApp } from '@symfony/stimulus-bridge';

export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
));
import { Tooltip, Toast, Popover } from 'bootstrap';
// start the Stimulus application
import './bootstrap';

const container = document.getElementById("exampleModal");
const modal = new bootstrap.Modal(container);

document.getElementById("myInput").addEventListener("click", function () {
    modal.show();
});
document.getElementById("close").addEventListener("click", function () {
    modal.hide();
});
document.getElementById("closeFooter").addEventListener("click", function () {
    modal.hide();
});
