<template>
  <div>
    <input type="hidden" v-model="value.content" ref="place_content" id="field_content" />
    <div class="input-group">
      <text-input class="w-full" type="text" v-model="value.name" id="field_name" />
      <button @click="fetchData(value.name)" target="_blank"
        class="input-group-append flex items-center hover:bg-grey-40 hover:text-grey-70">
        Fetch!
      </button>
    </div>
    <div ref="fetch_status" class="py-1 text-small"></div>
  </div>
</template>
<script>
/**
 * Register this component with:
 * Vue.component('google_place-fieldtype', require('./path/to/GooglePlace.vue'))
 */
export default {
  mixins: [Fieldtype],

  data() {
    return {
      //
    };
  },
  created() {
    if (!this.value) {
      this.value = {
        name: '',
        content: '',
      };
    }
  },
  mounted() {
    if (!this.value.name) {
      this.$refs.fetch_status.innerHTML = "Provide a Google Place Id or a search string";
    }
  },
  watch: {
    value: {
      deep: true,
      handler: function (newValue) {
        this.update(newValue);
      },
    },
  },
  methods: {
    fetchData: function (value) {
      fetch(this.meta.fetch_url + "/" + value, {
        method: "GET",
      })
        .then((response) => {
          if (response.ok) {
            return response.json();
          } else {
            return "Server returned " + response.status + " : " + response.statusText;
          }
        })
        .then((response) => {
          if (response.data.result) {
            const result = response.data.result;
            const placeToField = {
              title: result.vicinity,
              location_lat: result.geometry.location.lat,
              location_lng: result.geometry.location.lng,
              main_website: result.website,
              main_tel: result.international_phone_number,
              main_address: result.formatted_address,
              google_maps_place_id: result.place_id,
              google_maps_url: result.url,
            };
            for (const [key, value] of Object.entries(placeToField)) {
              this.$store.dispatch("publish/base/setFieldValue", {
                handle: key,
                user: Statamic.user.id,
                value: value,
              });
            }
            // this.value.content = JSON.stringify(response.data.result);
            this.$refs.fetch_status.innerHTML = "Data fetched successfully: <em>" + response.data.result.name + " (" + response.data.result.formatted_address + ")</em>";
          } else {
            this.$refs.fetch_status.innerHTML = "No data found for the requested search";
          }
        })
        .catch((err) => {
          this.$refs.fetch_status.innerHTML = "No data found for the requested search";
        });
    },
  },
};
</script>
