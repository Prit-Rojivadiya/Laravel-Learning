<template>
  <v-autocomplete
    :value="value"
    :items="items"
    :loading="isLoading"
    :search-input.sync="search"
    cache-items
    item-text="name"
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
  props: ['value', 'prefill', 'search_label', 'api_url_base', 'includeAllSelection' ,'additionalParams'],
  data: () => ({
    isLoading: false,
    items: [],
    valid: true,
    search: null,
    select: null,
    queryParams: null,
  }),
  async created () {
    if (this.prefill) {
      this.isLoading = 'loading'
      this.queryParams = {
        params: {
          filterByName: this.search,
          paginate: false
        }
      }
      if (this.additionalParams) {
        for (const key in this.additionalParams) {
          this.queryParams.params[key] = this.additionalParams[key]
        }
      }
      const result = await this.$axios.$get(`/${this.api_url_base}`, this.queryParams)
      if (Array.isArray(result.data)) {
        this.items = result.data
      }
      else {
        this.items = result
      }
      if (this.includeAllSelection) {
        this.items.unshift ({id: 'all', name: "All"})
      }
      this.isLoading = false
    }
  },
  watch: {
    async search (val) {
      // Items have already been requested
      if (this.isLoading) return

      if (val !== this.value.name) {
        this.isLoading = 'loading'
        const result = await this.$axios.$get(`/${this.api_url_base}`, {
          params: {
            filterByName: this.search,
            paginate: false
          }
        })
        this.items = result.data
        if (this.includeAllSelection) {
          this.items.unshift ({id: 'all', name: "All"})
        }
        this.isLoading = false
      }
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
