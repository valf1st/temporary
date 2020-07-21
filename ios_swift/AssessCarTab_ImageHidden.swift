//
//  AssessCarTab_ImageHidden.swift
//  insmart_ios
//
//  Created by FukudaAkali on 23/10/2019.
//  Copyright © 2019 **********. All rights reserved.
//

import RealmSwift

// 画像
class AssessCarTab_ImageHidden: Object {
    // 「一時保存」「登録」していないローカルのものはUUIDを入れておく
    @objc dynamic var car_id: String? = UUID().uuidString
    // 画像データ(base64)
    @objc dynamic internal var image: String? = nil
    // 画像拡張子
    @objc dynamic internal var image_extension: String? = nil
    // 画像コメント
    @objc dynamic internal var image_comment: String? = nil
    
    convenience init(car_id: String?, image: String?, image_extension: String?, comment: String?) {
        self.init()
        self.car_id = car_id
        self.image = image
        self.image_extension = image_extension
        self.image_comment = comment
    }
}
