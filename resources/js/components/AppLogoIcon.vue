<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/assets/logoAPP/logo_pokecardmaster.png"
        alt="PokeCard Master"
        :class="className"
        v-bind="$attrs"
    />
</template>
