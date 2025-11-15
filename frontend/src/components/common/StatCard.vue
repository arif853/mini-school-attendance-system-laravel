<script setup>
const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  hint: { type: String, default: '' },
  accent: { type: String, default: 'var(--color-accent-soft)' },
})
</script>

<template>
  <article class="stat-card" :style="{ borderColor: accent }">
    <p class="stat-card__label">{{ label }}</p>
    <p class="stat-card__value">{{ value }}</p>
    <p v-if="hint" class="stat-card__hint">{{ hint }}</p>
  </article>
</template>

<style scoped>
.stat-card {
  border: 1px solid var(--color-border);
  border-left-width: 4px;
  border-radius: 0.9rem;
  padding: 1rem;
  background: var(--color-background);
}

.stat-card__label {
  font-size: 0.85rem;
  color: var(--color-text-soft);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.stat-card__value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0.4rem 0;
}

.stat-card__hint {
  color: var(--color-text-soft);
  font-size: 0.85rem;
}
</style>
