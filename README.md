# myCred Investments
It allows admin to get myCred point investments on the site using WooCommerce plugin. 

### Notes
- This plugin requires WooCommerce, AffiiateWP and myCred to be installed & configured in order to perform the functions.
- Since, it's a custom plugin made for a specific project therefore, use it on your own.

### Detail
Once your have configured WooCommerce, AffiliateWP & myCred and activated "mycred investments" add-on, you will see a custom menu in admin section named "Investments Stats". There will be three different sections under this menu.
 
1) Investment Statistics: 
Here admin can see 
- Total Coins: For all the registered users
- Total Invested: Total invested Points from all the registered users
- Active Investments: Active Investments of all the registered users
- Earnings: Total investment earnings of all the registered users
- Referrals: Total referral earning of all the registered users.

Admin will see also these stats in chart presentation.

2) Settings:
Here admin can see the avaialble shortcode of the plugin with a short documentation. Available shortcodes are:
- [mci_coins_balance] : To display coins for logged in user
- [mci_earning_investments] : To display investment earnings of current logged in user
- [mci_active_investments] : To display active investment of current logged in user
- [mci_referral_earnings] : To display referral earnings of current logged in user
- [mci_earning_chart] : To display investment, earnings, referrals earnings of current logged in user in chart view
- [mci_invested] : To display total invested amount for the logged in user
- [mci_user_investment_record] : To display logged in users investments

3) Payment Order:
Here admin can see all the investments of users. The investment lists can be filtered through "user id", "user name", "Investment Date", "Order Number", "Payment Number" and "Amount Status". Admin can also update "next profit date & amount" too from the list.

### Creating Investment Product
After activating the add-on admin will create investment product in WooCommerce. The add-on adds a new product type "Investments", on selecting this prodct type new additional options appears like investment terms, invetment type, minimum amount to be invested etc. Admin will fill all the options and will save the product. That's it.

Once user purchase the investment product, it will create a new entry in the investment table with status on-hold, admin will look into the investment detail and will manually approve it to be processed.




