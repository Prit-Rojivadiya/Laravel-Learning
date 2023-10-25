<template>
  <v-select
    :value="value"
    :items="options"
    label="Gender"
    required
    dense
    @input="updateValue"
    v-bind="$attrs"
  />
</template>

<script>
export default {
  props: {
    value: {
      type: String
    }
  },
  data: () => ({
    options: [{ 'text': 'Female', 'value': 'F' }, { 'text': 'Male', 'value': 'M' }]
  }),
  methods: {
    updateValue (value) {
      this.$emit('input', value)
    }
  }
}
</script>
