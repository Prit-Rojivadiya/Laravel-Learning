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
          {{ line_item_type && line_item_type.id ? 'Edit' : 'New' }} Line Item Type
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveLineItemType()">
            {{ line_item_type && line_item_type.id ? 'Save' : 'Create' }} Line Item Type
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
            <v-text-field
              v-model="form.name"
              :rules="[v => !!v || 'Please enter a unique name for this line item type']"
              label="Name"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.code"
              :rules="[v => !!v || 'Please enter a unique code for this line item type']"
              label="Code"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
            <v-col lg="6" sm="6" xs="12" dense>
              <v-text-field
                v-model="form.desc"
                :rules="[v => !!v || 'Please enter line item type description']"
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
  name: "ManageLineItemType",
  props: ['value', 'line_item_type'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    errors: [],
    form: {
      name: null,
      code: null,
      desc: null,
    }
  }),
  async created () {
    if (this.line_item_type && this.line_item_type.id) {
      this.form.name = this.line_item_type.name
      this.form.code = this.line_item_type.code
      this.form.desc = this.line_item_type.desc
    }
  },
  methods: {
    async saveLineItemType () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.line_item_type && this.line_item_type.id) {
          await this.$axios.$put(`/line_item_types/${this.line_item_type.id}`, this.form)
        } else {
          await this.$axios.$post(`/line_item_types`, this.form)
        }
        this.$emit('line_item_type-saved')
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
