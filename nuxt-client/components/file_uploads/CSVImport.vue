<template>
  <div class="tranzit-csv-import">
    <VueCsvImport
      v-model="csvData"
      :mapFields="mapFields"
      :autoMatchFields="true"
      :autoMatchIgnoreCase="true"
      :callback="finishedParsing"
      :tableClass="'csvtable'"
      :canIgnore="true"
      :ignoreOptionText="'Ignore'"
      :fileMimeTypes="['text/csv']"
    >
      <template slot="hasHeaders" slot-scope="{headers, toggle}">
        <v-row class="mb-1">
          <v-col cols="12">
            <label>
              <input type="checkbox" id="hasHeaders" :value="headers" @change="toggle">
              CSV file contains headers?
            </label>
          </v-col>
        </v-row>
      </template>

      <template slot="error">
        <v-row class="mb-1">
          <v-col cols="12">
            File type is invalid
          </v-col>
        </v-row>
      </template>

      <template slot="next" slot-scope="{load}">
        <v-row class="mt-2 mb-1">
          <v-col cols="12">
            <v-btn small style="float: left;" @click.prevent="load">Preview Import</v-btn>
            <v-btn v-if="csvData" class="ml-2" small color="primary" style="float: left;" @click.prevent="importData">Run Import</v-btn>
          </v-col>
        </v-row>
      </template>

      <template slot="thead">
          <tr>
            <th>Field</th>
            <th>CSV Column</th>
          </tr>
      </template>

    </VueCsvImport>
  </div>
</template>
<script>

import { VueCsvImport } from 'vue-csv-import' //https://github.com/jgile/vue-csv-import/tree/vue2

export default {
  props:['mapFields'],
  components: {
    VueCsvImport,
  },
  data: () => ({
    csvData: null,
  }),
  created() {
  },
  methods: {
    finishedParsing (val) {
      this.$emit('preview-csv', this.csvData)
    },
    importData() {
      this.$emit('import-csv', this.csvData)
    },
  }
}
</script>
<style scoped>

</style>
