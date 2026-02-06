<script setup lang="ts">
import type { DateValue } from '@internationalized/date'
import {
  DateFormatter,
  getLocalTimeZone,
  parseDate,
} from '@internationalized/date'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'

interface Props {
  placeholder?: string
  min?: string
  max?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Pick a date'
})

const modelValue = defineModel<string>({ required: true })

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
})

// Convert string to DateValue for calendar
const dateValue = computed<DateValue | undefined>({
  get: () => {
    if (!modelValue.value) return undefined
    try {
      return parseDate(modelValue.value)
    } catch {
      return undefined
    }
  },
  set: (newValue) => {
    if (!newValue) {
      modelValue.value = ''
      return
    }
    // Convert DateValue to YYYY-MM-DD string
    const year = newValue.year
    const month = String(newValue.month).padStart(2, '0')
    const day = String(newValue.day).padStart(2, '0')
    modelValue.value = `${year}-${month}-${day}`
  }
})

// Min/max date values for calendar
const minValue = computed(() => {
  if (!props.min) return undefined
  try {
    return parseDate(props.min)
  } catch {
    return undefined
  }
})

const maxValue = computed(() => {
  if (!props.max) return undefined
  try {
    return parseDate(props.max)
  } catch {
    return undefined
  }
})

// Display formatted date
const displayValue = computed(() => {
  if (!dateValue.value) return props.placeholder
  try {
    return df.format(dateValue.value.toDate(getLocalTimeZone()))
  } catch {
    return props.placeholder
  }
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :disabled="disabled"
        :class="cn(
          'w-full justify-start text-left font-normal',
          !dateValue && 'text-muted-foreground',
        )"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{ displayValue }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <Calendar
        v-model="dateValue"
        :min-value="minValue"
        :max-value="maxValue"
        :disabled="disabled"
        initial-focus
      />
    </PopoverContent>
  </Popover>
</template>
