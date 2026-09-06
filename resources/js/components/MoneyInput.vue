<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Input } from "@/components/ui/input";

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        name: string;
        defaultValue?: string | number;
        modelValue?: string | number;
        locale?: string;
    }>(),
    {
        defaultValue: "",
        modelValue: undefined,
        locale: "en-US",
    },
);

const emit = defineEmits<{
    "update:modelValue": [value: number | null];
}>();

function normalize(value: string | number | undefined): string {
    return String(value ?? "")
        .replace(/\D/g, "")
        .replace(/^0+(?=\d)/, "");
}

const rawValue = ref(normalize(props.modelValue ?? props.defaultValue));
const formattedValue = computed(() =>
    rawValue.value === "" ? "" : new Intl.NumberFormat(props.locale).format(Number(rawValue.value)),
);

watch(
    () => props.modelValue,
    (value) => {
        if (value !== undefined) {
            rawValue.value = normalize(value);
        }
    },
);

function updateValue(value: string | number): void {
    rawValue.value = normalize(value);
    emit("update:modelValue", rawValue.value === "" ? null : Number(rawValue.value));
}
</script>

<template>
    <div>
        <Input
            v-bind="$attrs"
            :model-value="formattedValue"
            type="text"
            inputmode="numeric"
            autocomplete="off"
            @update:model-value="updateValue"
        />
        <input type="hidden" :name="name" :value="rawValue" />
    </div>
</template>
