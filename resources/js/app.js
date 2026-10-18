import './bootstrap';

import Alpine from 'alpinejs';
import lottie from 'lottie-web/build/player/lottie_light';

window.Alpine = Alpine;

Alpine.start();

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function updateParallax() {
    document.querySelectorAll('[data-parallax-speed]').forEach((element) => {
        if (reducedMotion.matches) {
            element.style.setProperty('--parallax-offset', '0px');
            return;
        }

        const speed = Number(element.dataset.parallaxSpeed);
        const top = element.getBoundingClientRect().top + window.scrollY;
        const offset = (window.scrollY - top) * speed;
        element.style.setProperty('--parallax-offset', `${offset.toFixed(1)}px`);
    });
}

let parallaxFrame = 0;
function requestParallaxUpdate() {
    if (parallaxFrame) {
        return;
    }

    parallaxFrame = window.requestAnimationFrame(() => {
        updateParallax();
        parallaxFrame = 0;
    });
}

if (document.querySelector('[data-parallax-speed]')) {
    updateParallax();
    window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
    window.addEventListener('resize', requestParallaxUpdate, { passive: true });
    reducedMotion.addEventListener('change', requestParallaxUpdate);
}

if (!reducedMotion.matches) {
    document.querySelectorAll('[data-lottie-src]').forEach((container) => {
        lottie.loadAnimation({
            container,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: container.dataset.lottieSrc,
            rendererSettings: {
                preserveAspectRatio: 'xMidYMid meet',
                progressiveLoad: true,
            },
        });
    });
}

const scrollToTopButton = document.querySelector('.scroll-to-top');
if (scrollToTopButton) {
    const updateScrollButton = () => {
        scrollToTopButton.hidden = window.scrollY < 500;
    };

    updateScrollButton();
    window.addEventListener('scroll', updateScrollButton, { passive: true });
    scrollToTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
    });
}

const pointerIndicator = document.querySelector('.marketing-pointer');
if (pointerIndicator && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    document.addEventListener('pointermove', (event) => {
        pointerIndicator.style.setProperty('--pointer-x', `${event.clientX}px`);
        pointerIndicator.style.setProperty('--pointer-y', `${event.clientY}px`);
        pointerIndicator.classList.add('marketing-pointer-visible');
    }, { passive: true });

    document.addEventListener('pointerover', (event) => {
        pointerIndicator.classList.toggle('marketing-pointer-interactive', Boolean(event.target.closest('a, button, summary, input, select, textarea')));
    }, { passive: true });
}

function showPayPalMessage(container, message) {
    const status = container.parentElement.querySelector('[data-paypal-message]');
    if (!status) {
        return;
    }

    status.textContent = message;
    status.hidden = false;
}

document.querySelectorAll('[data-paypal-buttons]').forEach((container) => {
    if (!window.paypal) {
        showPayPalMessage(container, 'Checkout could not load. Please refresh or contact our team.');
        return;
    }

    let paymentId;
    let orderId;
    window.paypal.Buttons({
        style: { layout: 'vertical', shape: 'rect', label: 'paypal', height: 42 },
        createOrder: async () => {
            const response = await fetch(container.dataset.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    plan: container.dataset.plan,
                    period: container.dataset.period,
                }),
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Checkout could not be started.');
            }

            paymentId = result.payment_id;
            orderId = result.id;
            return orderId;
        },
        onApprove: async (data) => {
            if (!paymentId || data.orderID !== orderId) {
                throw new Error('This payment could not be matched to the current checkout.');
            }

            const response = await fetch(container.dataset.captureUrl.replace('__PAYMENT_ID__', encodeURIComponent(paymentId)), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Payment could not be confirmed.');
            }

            window.location.assign('/dashboard');
        },
        onCancel: () => showPayPalMessage(container, 'Checkout canceled. You can try again whenever you’re ready.'),
        onError: (error) => {
            showPayPalMessage(container, error instanceof Error ? error.message : 'Checkout could not be completed. Please try again.');
        },
    }).render(container).catch((error) => {
        showPayPalMessage(container, error instanceof Error ? error.message : 'Checkout could not be loaded.');
    });
});

const dealAuditorForm = document.querySelector('[data-deal-auditor]');
if (dealAuditorForm) {
    const notesField = dealAuditorForm.querySelector('[name="notes"]');
    const wordCount = dealAuditorForm.querySelector('[data-word-count]');
    const submitButton = dealAuditorForm.querySelector('button[type="submit"]');
    const submitLabel = dealAuditorForm.querySelector('[data-submit-label]');
    const errorMessage = dealAuditorForm.querySelector('[data-demo-error]');
    const results = document.querySelector('[data-demo-results]');
    const emptyState = results.querySelector('[data-demo-empty]');
    const output = results.querySelector('[data-demo-output]');
    const scenarios = {
        risk: 'Quarterly renewal review. The customer champion left the company last month. The security review is still unanswered, and procurement approval is delayed. Active usage declined over the last six weeks. The renewal is scheduled for next quarter.',
        expansion: 'Monthly account review. Product usage grew 38% this quarter and active users expanded to two new teams. Available license seats are near the cap. The VP of Operations joined the review and is supportive of expanding the workflow.',
        unresponsive: 'Renewal check-in notes. We sent two follow-up emails and the buyer is not responding. The decision meeting is pending, and the procurement team has not confirmed the approval timeline.',
    };

    const updateWordCount = () => {
        const words = notesField.value.trim().match(/\S+/g) || [];
        wordCount.textContent = `${words.length} words`;
    };

    const addText = (parent, tag, className, text) => {
        const element = document.createElement(tag);
        if (className) {
            element.className = className;
        }
        element.textContent = text;
        parent.append(element);
        return element;
    };

    const renderSignals = (container, signals) => {
        container.replaceChildren();
        signals.forEach((signal) => {
            const card = document.createElement('article');
            card.className = `deal-signal-card deal-signal-${signal.type}`;
            const top = document.createElement('div');
            top.className = 'deal-signal-heading';
            addText(top, 'strong', '', signal.label);
            addText(top, 'span', `deal-severity-${signal.severity}`, signal.severity);
            card.append(top);
            addText(card, 'p', 'deal-signal-evidence-label', 'Matched evidence');
            addText(card, 'blockquote', '', `“${signal.evidence}”`);
            addText(card, 'p', 'deal-signal-action', signal.action);
            container.append(card);
        });
    };

    const scenariosContainer = document.querySelector('.deal-scenario-list');
    scenariosContainer.addEventListener('click', (event) => {
        const button = event.target.closest('[data-demo-scenario]');
        if (!button) {
            return;
        }

        notesField.value = scenarios[button.dataset.demoScenario] || '';
        updateWordCount();
        notesField.focus();
    });

    notesField.addEventListener('input', updateWordCount);
    updateWordCount();

    dealAuditorForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorMessage.hidden = true;
        results.setAttribute('aria-busy', 'true');
        submitButton.disabled = true;
        submitLabel.textContent = 'Reviewing signals…';

        try {
            const response = await fetch(dealAuditorForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': dealAuditorForm.querySelector('[name="_token"]').value,
                },
                body: JSON.stringify({ notes: notesField.value }),
            });
            const result = await response.json();
            if (!response.ok) {
                const firstError = Object.values(result.errors || {})[0];
                throw new Error(firstError?.[0] || result.message || 'The demo could not analyze these notes.');
            }

            emptyState.hidden = true;
            output.hidden = false;
            output.querySelector('[data-score-value]').textContent = result.score;
            output.querySelector('[data-score-gauge]').style.setProperty('--score-angle', `${result.score * 3.6}deg`);
            output.querySelector('[data-health-label]').textContent = result.health;
            output.querySelector('[data-score-context]').textContent = `${result.risks.length} risk signal${result.risks.length === 1 ? '' : 's'} · ${result.opportunities.length} opportunity signal${result.opportunities.length === 1 ? '' : 's'} detected`;
            output.querySelector('[data-processing-time]').textContent = `${result.metrics.processing_ms} ms`;
            output.querySelector('[data-input-words]').textContent = `${result.metrics.input_words} words`;
            const riskGroup = output.querySelector('[data-risk-group]');
            riskGroup.hidden = result.risks.length === 0;
            riskGroup.querySelector('[data-risk-count]').textContent = result.risks.length;
            renderSignals(riskGroup.querySelector('[data-risk-signals]'), result.risks);
            const opportunityGroup = output.querySelector('[data-opportunity-group]');
            opportunityGroup.hidden = result.opportunities.length === 0;
            opportunityGroup.querySelector('[data-opportunity-count]').textContent = result.opportunities.length;
            renderSignals(opportunityGroup.querySelector('[data-opportunity-signals]'), result.opportunities);
            const recommendations = output.querySelector('[data-recommendations]');
            recommendations.replaceChildren();
            result.recommendations.forEach((recommendation) => addText(recommendations, 'li', '', recommendation));
            if (result.recommendations.length === 0) {
                addText(recommendations, 'li', '', 'Review the account context with the customer team and confirm current priorities.');
            }
            output.querySelector('[data-outreach]').textContent = result.outreach;
            output.querySelector('[data-result-notice]').textContent = result.notice;
        } catch (error) {
            errorMessage.textContent = error instanceof Error ? error.message : 'The demo could not analyze these notes.';
            errorMessage.hidden = false;
        } finally {
            results.setAttribute('aria-busy', 'false');
            submitButton.disabled = false;
            submitLabel.textContent = 'Analyze evidence';
        }
    });
}
