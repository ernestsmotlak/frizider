<script setup lang="ts">
// Nothing yet: the balance and ledger endpoint is not built. This panel stays
// a frame on purpose rather than guessing at numbers.
</script>

<template>
    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Credits</h2>
            <p class="panel-caption">Generations left</p>
        </div>

        <div class="placeholder">
            <div class="placeholder-icon" aria-hidden="true">🎟️</div>
            <p class="placeholder-title">Not wired up yet</p>
            <p class="placeholder-copy">
                Your remaining generations will show here, along with every credit spent and
                every one refunded when a generation failed.
            </p>
        </div>
    </section>
</template>

<style scoped>
.panel-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.panel-title {
    font-size: 1.12rem;
    font-weight: 750;
    letter-spacing: -0.01em;
    color: var(--text-strong);
}

.panel-caption {
    font-size: 0.76rem;
    color: var(--text-muted);
}

.placeholder {
    padding: 1.6rem 1.1rem;
    text-align: center;
    border-radius: 1rem;
    border: 1px dashed color-mix(in srgb, var(--line-strong) 70%, white 30%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(250, 255, 251, 0.9) 100%);
}

.placeholder-icon {
    font-size: 1.5rem;
    line-height: 1;
}

.placeholder-title {
    margin-top: 0.55rem;
    font-size: 0.94rem;
    font-weight: 700;
    color: var(--text-strong);
}

.placeholder-copy {
    margin-top: 0.28rem;
    font-size: 0.8rem;
    line-height: 1.45;
    color: var(--text-muted);
}
</style>
