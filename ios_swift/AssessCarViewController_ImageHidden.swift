//
//  AssessCarViewController_ImageHidden.swift
//  insmart_ios
//
//  Created by FukudaAkali on 23/10/2019.
//  Copyright © 2019 **********. All rights reserved.
//

import RealmSwift

// 画像選択画面
class AssessCarViewController_ImageHidden: UIViewController {
    
    @IBOutlet weak var collectionView: UICollectionView!
    @IBOutlet weak var layout: UICollectionViewFlowLayout!
    @IBOutlet weak var selectView: AssessImageSelectView!
    
    // 保存する画像一覧データ
    internal var imagehiddens: List<AssessCarTab_ImageHidden>!
    // 表示する画像一覧データ
    var imageHiddenArray: [(image: UIImage, isEdit: Bool)] = []
    var selectRow = 0
    
    let MARGIN: CGFloat = 20
    // 画像の総数
    let IMAGE_H_COUNT = 2
    
    override func viewDidLoad() {
        super.viewDidLoad()
        self.initialize()
        self.showImages()
    }
    
    override func viewDidAppear(_ animated: Bool) {
        super.viewDidAppear(animated)
        self.selectView.libraryAuth()
    }
}

extension AssessCarViewController_ImageHidden {
    private func initialize() {
        self.collectionView.delegate = self
        self.collectionView.dataSource = self
        self.collectionView.register(cellType: AssessImageCell.self)
        let cellWidth = (self.view.bounds.width - (self.MARGIN * 5)) / 2
        let scale: CGFloat = 210 / 154
        self.layout.itemSize = CGSize(width: cellWidth, height: cellWidth * scale)
        self.layout.minimumInteritemSpacing = self.MARGIN
        self.layout.minimumLineSpacing = self.MARGIN
        self.layout.sectionInset = UIEdgeInsets(top: self.MARGIN, left: self.MARGIN, bottom: self.MARGIN, right: self.MARGIN)
        self.collectionView.collectionViewLayout = layout
        //self.selectView.delegate = self
    }
}

extension AssessCarViewController_ImageHidden {
    // 車検証の画像をセットする
    internal func setCertificateImage() {
        //self.imageHiddenArray.append((UIImage(named: "noImgSD_1") ?? UIImage(), false))
    }
}

extension AssessCarViewController_ImageHidden: UICollectionViewDataSource {
    func collectionView(_ collectionView: UICollectionView, numberOfItemsInSection section: Int) -> Int {
        return IMAGE_H_COUNT + 1
    }
    func collectionView(_ collectionView: UICollectionView, cellForItemAt indexPath: IndexPath) -> UICollectionViewCell {
        let cell = self.collectionView.dequeueReusableCell(with: AssessImageCell.self, for: indexPath)
        cell.setup(indexPath: indexPath)
        
        return cell
    }
}

extension AssessCarViewController_ImageHidden: UICollectionViewDelegate {
    func collectionView(_ collectionView: UICollectionView, didSelectItemAt indexPath: IndexPath) {

    }
}

extension AssessCarViewController_ImageHidden: AssessImageCellDelegate {
    func reloadCell(indexPath: IndexPath) {
        self.collectionView.reloadData()
    }
    // テキストを入力した時に呼ばれる
    func textFieldDidEndEditing(cell: AssessImageCell, row: Int) {
        // データを更新する
        RealmDBManager.shared.write {
            if row <= 2 {
                self.imagehiddens[row].image_comment = cell.textField.text
            }
        }
    }
}

extension AssessCarViewController_ImageHidden {
    // images の画像を表示する
    private func showImages() {

    }
}
