import moment from "moment";

let filters = {
    /**
     *
     * @param {string} value
     * @param {string} format
     * @returns
     */
    enFormat(value, format = "ll") {
        moment.locale("en-gb");
        const time = moment(String(value)).format(format);

        if (time == "Invalid date") {
            return "-";
        }

        return time;
    },

    /**
     *
     * @param {string} str
     * @returns
     */
    capitalize(str) {
        if (!str) return "-";
        return String(str)
            .replace(/and/gi, "&")
            .replace(/\-|\_/gi, " ")
            .replace(/([A-Z][^A-Z]+)/g, " $1")
            .split(" ")
            .map((x) => x.charAt(0).toUpperCase() + x.slice(1))
            .join(" ");
    },

    today() {
        return moment().format("D MMM, YYYY");
    },

    money(val) {
        return Number(val || 0).toFixed(2);
    },

    formatBDT(amount) {
        if (!amount || isNaN(amount)) return "৳ 0.00";

        return (
            "৳ " +
            Number(amount).toLocaleString("en-BD", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        );
    },

    numberToBanglaWords(number) {
        const ones = {
            0: "",
            1: "এক",
            2: "দুই",
            3: "তিন",
            4: "চার",
            5: "পাঁচ",
            6: "ছয়",
            7: "সাত",
            8: "আট",
            9: "নয়",
            10: "দশ",
            11: "এগারো",
            12: "বারো",
            13: "তেরো",
            14: "চৌদ্দ",
            15: "পনেরো",
            16: "ষোল",
            17: "সতেরো",
            18: "আঠারো",
            19: "উনিশ",
        };

        const tens = {
            2: "বিশ",
            3: "ত্রিশ",
            4: "চল্লিশ",
            5: "পঞ্চাশ",
            6: "ষাট",
            7: "সত্তর",
            8: "আশি",
            9: "নব্বই",
        };

        const units = [
            { value: 10000000, label: "কোটি" },
            { value: 100000, label: "লক্ষ" },
            { value: 1000, label: "হাজার" },
            { value: 100, label: "শত" },
        ];

        function convert(n) {
            n = parseInt(n);

            if (n < 20) return ones[n];

            if (n < 100) {
                return (
                    tens[Math.floor(n / 10)] +
                    (n % 10 ? " " + ones[n % 10] : "")
                );
            }

            for (let i = 0; i < units.length; i++) {
                if (n >= units[i].value) {
                    let count = Math.floor(n / units[i].value);
                    let remainder = n % units[i].value;

                    return (
                        convert(count) +
                        " " +
                        units[i].label +
                        (remainder ? " " + convert(remainder) : "")
                    );
                }
            }
        }

        // =========================
        // Split taka & paisa
        // =========================
        number = Number(number).toFixed(2);
        let [taka, paisa] = number.split(".");

        taka = parseInt(taka);
        paisa = parseInt(paisa);

        let takaWords = taka > 0 ? convert(taka) + " টাকা" : "";
        let paisaWords = paisa > 0 ? " " + convert(paisa) + " পয়সা" : "";

        return (takaWords + paisaWords).trim() + ".";
    },

    numberToEnglishBD(amount) {
        const ones = [
            "",
            "One",
            "Two",
            "Three",
            "Four",
            "Five",
            "Six",
            "Seven",
            "Eight",
            "Nine",
            "Ten",
            "Eleven",
            "Twelve",
            "Thirteen",
            "Fourteen",
            "Fifteen",
            "Sixteen",
            "Seventeen",
            "Eighteen",
            "Nineteen",
        ];

        const tens = [
            "",
            "",
            "Twenty",
            "Thirty",
            "Forty",
            "Fifty",
            "Sixty",
            "Seventy",
            "Eighty",
            "Ninety",
        ];

        function convertBelowThousand(n) {
            let str = "";

            if (n >= 100) {
                str += ones[Math.floor(n / 100)] + " Hundred ";
                n = n % 100;
            }

            if (n >= 20) {
                str += tens[Math.floor(n / 10)] + " ";
                n = n % 10;
            }

            if (n > 0) {
                str += ones[n] + " ";
            }

            return str.trim();
        }

        function convert(n) {
            let result = "";

            if (n >= 10000000) {
                result += convert(Math.floor(n / 10000000)) + " Crore ";
                n = n % 10000000;
            }

            if (n >= 100000) {
                result += convert(Math.floor(n / 100000)) + " Lakh ";
                n = n % 100000;
            }

            if (n >= 1000) {
                result += convert(Math.floor(n / 1000)) + " Thousand ";
                n = n % 1000;
            }

            if (n > 0) {
                result += convertBelowThousand(n);
            }

            return result.trim();
        }

        // =========================
        // Split integer & decimal
        // =========================
        amount = Number(amount).toFixed(2);
        let [taka, paisa] = amount.split(".");

        taka = parseInt(taka);
        paisa = parseInt(paisa);

        let words = "";

        if (taka > 0) {
            words += convert(taka) + " Taka";
        } else {
            words += "Zero Taka";
        }

        if (paisa > 0) {
            words += " and " + convertBelowThousand(paisa) + " Paisa";
        }

        return words + " Only";
    },
};

export default {
    install: function (app) {
        app.config.globalProperties.$filter = filters;
    },
};
