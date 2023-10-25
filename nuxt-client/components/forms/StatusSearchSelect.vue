<template>
  <v-autocomplete
    :value="value"
    :items="items"
    :loading="isLoading"
    :search-input.sync="search"
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
  props: ['value', 'prefill', 'search_label', 'pmStatusList', 'api_url_base'],
  data: () => ({
    isLoading: false,
    items: [],
    valid: true,
    search: null
  }),
  async created () {
    if (this.prefill && this.api_url_base) {
      this.isLoading = 'loading'
      const result = await this.$axios.$get(`/${this.api_url_base}`, { params: { filterByName: this.search, paginate: false} })
      this.items = result.data
    }
    else if (this.pmStatusList) {
      this.items = this.pmStatusList
    }

  },
  watch: {
    async search (val) {
      // Items have already been requested
      // if (this.isLoading) return

      if (this.prefill && this.api_url_base) {
        this.isLoading = 'loading'
        const result = await this.$axios.$get(`/${this.api_url_base}`, {
          params: {
            filterByName: this.search,
            paginate: false
          }
        })
        this.items = result.data
      }
      else if (this.pmStatusList) {
        this.items = this.pmStatusList
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
