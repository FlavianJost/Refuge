import { Controller } from '@hotwired/stimulus';

/*
 * Interroge la messagerie toutes les quelques secondes et ajoute les nouveaux
 * messages à la discussion, ce qui donne l'impression d'un échange en direct.
 */
export default class extends Controller {
    static targets = ['list'];
    static values = {
        url: String,
        after: Number,
        interval: { type: Number, default: 4000 },
    };

    connect() {
        this.scrollToBottom();
        this.timer = window.setInterval(() => this.poll(), this.intervalValue);
    }

    disconnect() {
        window.clearInterval(this.timer);
    }

    async poll() {
        try {
            const url = new URL(this.urlValue, window.location.origin);
            url.searchParams.set('after', this.hasAfterValue ? this.afterValue : 0);

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (data.html && data.html.trim() !== '') {
                this.listTarget.insertAdjacentHTML('beforeend', data.html);
                this.scrollToBottom();
            }

            if (data.lastId) {
                this.afterValue = data.lastId;
            }
        } catch (error) {
            // Les erreurs passagères sont ignorées, l'appel suivant réessaiera.
        }
    }

    scrollToBottom() {
        this.element.scrollTop = this.element.scrollHeight;
    }
}
