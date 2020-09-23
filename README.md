1. Approve Combo (Calculate Combo Price)
    - Cal Total Price
        $combo->total_price = 
            ($combo->variant->price * $combo->amount) / $combo->variant->service->combo_ratio;
    - Add the expiry date to combo: 3 months from approve date
2. Approve intake
   * Loop all orders of intake
       - If order uses combo:
            + Minus combo
       - If order pays money
            + Calculate price and store to order
   * Add point to customer base on total price of intake (formula will be provided)
   * Calculate Final Price
       - If has discount: $final_price = $total_price - $discount_price
       - Else: $final_price = $total_price
   * Update status for intake to "is_valid = 1"
