<template>
  <v-dialog
    :value="value"
    @input="$emit('input')"
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
  >
    <v-card>
      <v-toolbar dark color="primary">
        <v-btn icon dark @click="$emit('input')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title>
          {{ line_item_category && line_item_category.id ? 'Edit' : 'New' }} Line Item Category
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveLineItemCategory()">
            {{ line_item_category && line_item_category.id ? 'Save' : 'Create' }} Line Item Category
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Please try again
      </v-alert>

      <v-form ref="form" v-model="formValid">
        <div class="overline px-4 pt-4 pb-2">Details</div>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-select
              v-model="form.line_item_type_id"
              :rules="[v => !!v || 'Please select the line item type']"
              :items="line_item_types"
              item-value="id"
              item-text="name"
              label="Line Item Type"
              required
              dense
            />
          </v-col>
        </v-row>

        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.name"
              :rules="[v => !!v || 'Please enter a unique name for this line item category']"
              label="Name"
              required
              dense
            />
          </v-col>
        </v-row>
        <!--
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.code"
              label="Code"
              required
              dense
            />
          </v-col>
        </v-row>
        -->
        <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.desc"
                label="Description"
                required
                dense
              />
            </v-col>
        </v-row>

      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "ManageLineItemCategory",
  props: ['value', 'line_item_category', 'lineItemType'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    line_item_types: [],
    errors: [],
    form: {
      name: null,
      code: null,
      desc: null,
    }
  }),
  async created () {
    let tLineItemTypes = await this.$axios.$get(`/line_item_types?paginate=false`)
    if (tLineItemTypes && tLineItemTypes.data) {
      this.line_item_types = tLineItemTypes.data
    }

    if (this.lineItemType && this.lineItemType.id) {
      this.form.line_item_type_id = this.lineItemType.id
    }

    if (this.line_item_category && this.line_item_category.id) {
      this.form.name = this.line_item_category.name
      this.form.code = this.line_item_category.code
      this.form.desc = this.line_item_category.desc
      this.form.line_item_type_id = this.line_item_category.line_item_type_id
    }


  },
  methods: {
    async saveLineItemCategory () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.line_item_category && this.line_item_category.id) {
          await this.$axios.$put(`/line_item_categories/${this.line_item_category.id}`, this.form)
        } else {
          await this.$axios.$post(`/line_item_categories`, this.form)
        }
        this.$emit('line_item_category-saved')
        this.working = false
        this.$emit('input')
        this.reset()
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push ({msg: e})
        }
        this.working = false
      }
    },
    reset () {
      this.form = {
        name: null,
        desc: null,
      }
      this.$refs.form.resetValidation()
    }
  },
}
</script>

<style scoped>

</style>
