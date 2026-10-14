import { Controller } from '@hotwired/stimulus';

/*
 * Polls a messaging endpoint every few seconds and appends new messages
 * to the conversation thread, giving a near real-time feel.
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
            // Ignore transient errors; the next tick will retry.
        }
    }

    scrollToBottom() {
        this.element.scrollTop = this.element.scrollHeight;
    }
}
