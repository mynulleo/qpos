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

    currency(amount) {
        return this.formatBDT(amount);
    },

    formatCurrency(amount) {
        return this.formatBDT(amount);
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
        if (number === undefined || number === null || isNaN(number)) return "";

        const bangla0to99 = [
            "", "এক", "দুই", "তিন", "চার", "পাঁচ", "ছয়", "সাত", "আট", "নয়", "দশ",
            "এগারো", "বারো", "তেরো", "চৌদ্দ", "পনেরো", "ষোল", "সতেরো", "আঠারো", "উনিশ", "বিশ",
            "একুশ", "বাইশ", "তেইশ", "চব্বিশ", "পঁচিশ", "ছাব্বিশ", "সাতাশ", "আটাশ", "উনত্রিশ", "ত্রিশ",
            "একত্রিশ", "বত্রিশ", "তেত্রিশ", "চৌত্রিশ", "পঁয়ত্রিশ", "ছত্রিশ", "সাঁইত্রিশ", "আটত্রিশ", "ঊনচল্লিশ", "চল্লিশ",
            "একচল্লিশ", "বিয়াল্লিশ", "তেতাল্লিশ", "চুয়াল্লিশ", "পঁয়তাল্লিশ", "ছেচল্লিশ", "সাতচল্লিশ", "আটচল্লিশ", "ঊনপঞ্চাশ", "পঞ্চাশ",
            "একান্ন", "বায়ান্ন", "তিপ্পান্ন", "চুয়ান্ন", "পঞ্চান্ন", "ছাপ্পান্ন", "সাতান্ন", "আটান্ন", "ঊনষাট", "ষাট",
            "একষট্টি", "বাষট্টি", "তেষট্টি", "চৌষট্টি", "পঁয়ষট্টি", "ছেষট্টি", "সাতষট্টি", "আটষট্টি", "ঊনসত্তর", "সত্তর",
            "একাত্তর", "বাহাত্তর", "তিয়াত্তর", "চুয়াত্তর", "পঁচাত্তর", "ছিয়াত্তর", "সাতাত্তর", "আটাত্তর", "ঊনআশি", "আশি",
            "একাশি", "বিরাশি", "তিরাশি", "চুরাশি", "পঁচাশি", "ছিয়াশি", "সাতাশি", "আটাশি", "ঊননব্বই", "নব্বই",
            "একানব্বই", "বানব্বই", "তিরানব্বই", "চুরানব্বই", "পঁচানব্বই", "ছিয়ানব্বই", "সাতানব্বই", "আটানব্বই", "নিরানব্বই"
        ];

        const hundreds = [
            "", "একশত", "দুইশত", "তিনশত", "চারশত", "পাঁচশত", "ছয়শত", "সাতশত", "আটশত", "নয়শত"
        ];

        function convertNumber(n) {
            n = parseInt(n, 10);
            if (n <= 0) return "";
            if (n < 100) return bangla0to99[n] || "";

            let words = [];

            // কোটি (Crore - 1,00,00,000)
            if (n >= 10000000) {
                const crore = Math.floor(n / 10000000);
                words.push(convertNumber(crore) + " কোটি");
                n = n % 10000000;
            }

            // লক্ষ (Lakh - 1,00,000)
            if (n >= 100000) {
                const lakh = Math.floor(n / 100000);
                words.push(bangla0to99[lakh] + " লক্ষ");
                n = n % 100000;
            }

            // হাজার (Thousand - 1,000)
            if (n >= 1000) {
                const thousand = Math.floor(n / 1000);
                words.push(bangla0to99[thousand] + " হাজার");
                n = n % 1000;
            }

            // শত (Hundred - 100)
            if (n >= 100) {
                const hundred = Math.floor(n / 100);
                words.push(hundreds[hundred] || (bangla0to99[hundred] + " শত"));
                n = n % 100;
            }

            // 1 to 99
            if (n > 0) {
                words.push(bangla0to99[n]);
            }

            return words.join(" ");
        }

        // =========================
        // Split taka & paisa
        // =========================
        const numFixed = Number(number).toFixed(2);
        const [takaStr, paisaStr] = numFixed.split(".");

        const taka = parseInt(takaStr, 10) || 0;
        const paisa = parseInt(paisaStr, 10) || 0;

        let result = "";
        if (taka > 0) {
            result += convertNumber(taka) + " টাকা";
        } else if (paisa === 0) {
            result = "শূন্য টাকা";
        }

        if (paisa > 0) {
            result += (result ? " " : "") + bangla0to99[paisa] + " পয়সা";
        }

        return result.trim() ? result.trim() + "।" : "";
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
