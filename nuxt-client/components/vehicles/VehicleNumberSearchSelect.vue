<template>
  <v-autocomplete
    :value="value"
    :items="items"
    :loading="isLoading"
    :search-input.sync="search"
    item-text="vehicle_number"
    item-value="id"
    color="white"
    :label="search_label"
    placeholder="Start typing to Search"
    return-object
    @change="updateValue"
    v-bind="$attrs"
  />
</template>

<script>
export default {
  props: ['value', 'prefill', 'search_label', 'api_url_base'],
  data: () => ({
    isLoading: false,
    items: [],
    valid: true,
    search: null
  }),
  async created () {
    if (this.prefill) {
      this.isLoading = 'loading'
      const result = await this.$axios.$get(`/${this.api_url_base}`, { params: { filterByName: this.search, paginate: false} })
      this.items = result.data
    }
  },
  watch: {
    async search (val) {
      // Items have already been requested
      // if (this.isLoading) return

      this.isLoading = 'loading'

      const result = await this.$axios.$get(`/${this.api_url_base}`, { params: { filterByName: this.search, paginate: false} })

      this.items = result.data
    },
  },
  methods: {
    reset () {
    },
    updateValue (value) {
      this.$emit('input', value)
    }
  }
}
</script>
