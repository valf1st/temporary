//
//  APIMethods.swift
//  insmart_ios
//
//  Created by FukudaAkali on 24/09/2019.
//  Copyright © 2019 **********. All rights reserved.
//

import Foundation

class LoginAPI {
    class func request(groupCode: String, loginId: String, password: String, deviceToken: String? = nil, onSuccess: @escaping(LoginEntity) -> (), onError: @escaping(String) -> ()) {
        
        var p: [String : Any] = [:]
        
        p["group_code"] = groupCode
        p["login_id"] = loginId
        p["password"] = password
        p["device_type"] = "1"
        p["access_token"] = TokenData.accessToken.get()
        p["device_model"] = UIDevice.modelName
        p["os_version"] = UIDevice.current.systemVersion
        p["app_version"] = App.version
        
        if let deviceToken = deviceToken {
            p["device_token"] = deviceToken
        }
        
        var json = Data()
        do {
            json = try JSONSerialization.data(withJSONObject: p, options: [])
        } catch {
            print(error.localizedDescription)
        }
        
        let api = APIBase(path: App.API.login, method: .post, parameters: [:])
        api.requestJson(json: json, success: { (data) in
            do
            {
                let entity: LoginEntity = try JSONDecoder().decode(LoginEntity.self, from: data)
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました1")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

class LogoutAPI {
    class func request(onSuccess: @escaping(LogoutEntity) -> (), onError: @escaping(String) -> ()) {
        
        let p: [String : Any] = ["user_id": UserData.userToken.get(), "access_token": TokenData.accessToken.get()]
        
        var json = Data()
        do {
            json = try JSONSerialization.data(withJSONObject: p, options: [])
        } catch {
            print(error.localizedDescription)
        }
        
        let api = APIBase(path: App.API.logout, method: .post, parameters: [:])
        api.requestJson(json: json, success: { (data) in
            do
            {
                let entity = try JSONDecoder().decode(LogoutEntity.self, from: data)
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました2")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

class QrAPI {
    
    class func request(qr1: String? = nil, qr2: String? = nil, qr3: String? = nil, qr4: String? = nil, qr5: String? = nil, onSuccess: @escaping(QrEntity) -> (), onError: @escaping(String) -> ()) {
        
        var p: [String : Any] = ["user_id": UserData.userToken.get(), "access_token": TokenData.accessToken.get()]
        
        if let qr1 = qr1 {
            p["qr1"]  = qr1
        }
        
        if let qr2 = qr2 {
            p["qr2"]  = qr2
        }
        
        if let qr3 = qr3 {
            p["qr3"]  = qr3
        }
        
        if let qr4 = qr4 {
            p["qr4"]  = qr4
        }
        
        if let qr5 = qr5 {
            p["qr5"]  = qr5
        }
        
        
        let api = APIBase(path: App.API.qr, method: .get, parameters: p)
        api.request(success: { (data) in
            do
            {
                let entity: QrEntity = try JSONDecoder().decode(QrEntity.self, from: data)
                
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました3")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

// 車名取得API
class CarNamesAPI {
    class func request(carMakerName: String, model: String, modelNum: String, categoryNum: String, onSuccess: @escaping(CarNamesEntity) -> (), onError: @escaping(String) -> ()) {
        
        let p: [String : Any] = ["user_id": UserData.userToken.get(),
                                 "access_token": TokenData.accessToken.get(),
                                 "car_maker_name": carMakerName,
                                 "model": model,
                                 "model_num": modelNum,
                                 "category_num": categoryNum]
        
        let api = APIBase(path: App.API.carNames, method: .get, parameters: p)
        api.request(success: { (data) in
            do
            {
                let entity: CarNamesEntity = try JSONDecoder().decode(CarNamesEntity.self, from: data)
                
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました4")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }

}

// グレード取得API
class GetGradeAPI {
    class func request(carMakerCode: Int, carNameCode: String, model: String, modelNum: String, categoryNum: String, onSuccess: @escaping(CarGradeEntity) -> (), onError: @escaping(String) -> ()) {
        
        let p: [String : Any] = ["user_id": UserData.userToken.get(),
                                 "access_token": TokenData.accessToken.get(),
                                 "car_maker_code": carMakerCode,
                                 "car_name_code": carNameCode,
                                 "model": model,
                                 "model_num": modelNum,
                                 "category_num": categoryNum]
        
        let api = APIBase(path: App.API.getGrade, method: .get, parameters: p)
        api.request(success: { (data) in
            do
            {
                let entity: CarGradeEntity = try JSONDecoder().decode(CarGradeEntity.self, from: data)
                
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました5")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

class CarColorsAPI {
    class func request(carCode: Int, onSuccess: @escaping(CarColorsEntity) -> (), onError: @escaping(String) -> ()) {
        
        let p: [String : Any] = ["user_id": UserData.userToken.get(), "access_token": TokenData.accessToken.get(), "car_code": carCode]
        
        let api = APIBase(path: App.API.carColors, method: .get, parameters: p)
        api.request(success: { (data) in
            do
            {
                let entity: CarColorsEntity = try JSONDecoder().decode(CarColorsEntity.self, from: data)
                
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました6")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

// 車両諸元取得API
class CarSpecsAPI {
    class func request(car_maker_code: Int? = nil, model: String? = nil, car_grade_name: String? = nil, model_num: String? = nil, category_num: String? = nil, model_official: String? = nil, onSuccess: @escaping(CarSpecsObject) -> (), onError: @escaping(String) -> ()) {
        
        var p: [String : Any] = ["user_id": UserData.userToken.get(), "access_token": TokenData.accessToken.get()]

        if let car_maker_code = car_maker_code {
            p["car_maker_code"] = car_maker_code
        }
        if let model = model {
            p["model"] = model
        }
        if let car_grade_name = car_grade_name {
            p["car_grade_name"] = car_grade_name
        }
        if let model_num = model_num {
            p["model_num"] = model_num
        }
        if let category_num = category_num {
            p["category_num"] = category_num
        }
        if let model_official = model_official {
            p["model_official"] = model_official
        }
        
        
        let api = APIBase(path: App.API.carSpecs, method: .get, parameters: p)
        api.request(success: { (data) in
            do
            {
                let entity: CarSpecsObject = try JSONDecoder().decode(CarSpecsObject.self, from: data)
                
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

// フォーム用選択項目取得API
class SelectablesAPI {
    
    class func request(onSuccess: @escaping(SelectablesObject) -> (), onError: @escaping(String) -> ()) {
        
        let p: [String : Any] = ["user_id": UserData.userToken.get(), "access_token": TokenData.accessToken.get()]
        
        let api = APIBase(path: App.API.selectables, method: .get, parameters: p)
        api.request(success: { (data) in
            do
            {
                dump(data)
                let entity: SelectablesObject = try JSONDecoder().decode(SelectablesObject.self, from: data)
                dump(entity)
                if entity.status_code != 200
                {
                    onError(entity.errors?.error_message ?? "")
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}

class CarPostAPI {
    class func request(json: Data, onSuccess: @escaping(PostResponseEntity) -> (), onError: @escaping(String?) -> (), onErrorEntity: @escaping(PostResponseEntity) -> ())  {

        dump(json)

        let api = APIBase(path: App.API.cars, method: .post, parameters: [:])
        api.requestJson(json: json, success: { (data) in
            do
            {
                let entity: PostResponseEntity = try JSONDecoder().decode(PostResponseEntity.self, from: data)
                
                if entity.status_code == 400 {
                    // 400の時はエラーメッセージが複数入る場合があるので entity を渡す
                    onErrorEntity(entity)
                    return
                }
                else if entity.status_code != 200 {
                    onError(entity.errors?.error_message)
                    return
                }
                onSuccess(entity)
            }
            catch
            {
                onError("データの取得に失敗しました")
            }
        }) { (errorMessage) in
            onError(errorMessage)
        }
    }
}
